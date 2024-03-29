<?php

namespace DMK\Mktools\ErrorHandler;

/***************************************************************
 *  Copyright notice
 *
 * (c) 2021 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use DMK\Mktools\Exception\RuntimeException;
use DMK\Mktools\Utility\Misc;
use Sys25\RnBase\Configuration\Processor;
use Sys25\RnBase\Typo3Wrapper\Core\Error\ProductionExceptionHandler;
use Sys25\RnBase\Utility\Debug;
use Sys25\RnBase\Utility\Logger;
use Sys25\RnBase\Utility\Network;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ExceptionHandler.
 *
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class ExceptionHandler extends ProductionExceptionHandler
{
    /**
     * @var Processor
     */
    private $configurations;

    /**
     * @var array
     */
    private $exceptionPageExtensionConfiguration = [];

    /**
     * @var string
     */
    private $lockFilePath = '';

    /**
     * Constructs this exception handler - registers itself as the default exception handler.
     */
    public function __construct()
    {
        parent::__construct();

        $this->lockFilePath = Environment::getVarPath().'/lock/';
    }

    /**
     * @param \Exception|\Throwable $exception
     * @param string                $context
     *
     * @see ProductionExceptionHandler::writeLogEntries()
     */
    protected function writeLogEntriesEnvironment($exception, $context)
    {
        // RuntimeException wird nur von
        // ErrorHandler::handleError geworfen und wurde schon geloggt
        if ($exception instanceof RuntimeException) {
            return;
        }

        if (!$this->lockAcquired($exception, $context)) {
            return;
        }

        $this->writeLogEntriesByParent($exception, $context);
    }

    /**
     * @param \Exception|\Throwable $exception
     * @param string                $context
     *
     * @see ProductionExceptionHandler::writeLogEntries()
     */
    protected function writeLogEntriesByParent($exception, $context)
    {
        // warnungen beim Logging interessieren uns nicht. Ohne @ führt dies dazu dass
        // die Warnung beim Logging festgehalten wird, nicht aber die eigentliche
        // Meldung, wenn die Warnung vor dem Schreiben des Logs auftritt
        @parent::writeLogEntries($exception, $context);
    }

    /**
     * @param \Exception|\Throwable $exception
     * @param string                $context
     *
     * @return bool
     */
    protected function lockAcquired($exception, $context)
    {
        $lockFile = $this->getLockFileByExceptionAndContext($exception, $context);

        $lastCall = (int) trim(file_get_contents($lockFile));
        if ($lastCall > (time() - 60)) {
            return false; // Only logging once a minute per error
        }

        file_put_contents($lockFile, time()); // refresh lock

        return true;
    }

    /**
     * @param \Exception|\Throwable $exception
     * @param string                $context
     *
     * @return string
     */
    protected function getLockFileByExceptionAndContext($exception, $context)
    {
        $lockIdentifier = 'mktoolsExceptionLock_'.md5(
            $exception->getCode().$exception->getMessage().
                $exception->getPrevious().$context
        );

        $lockFile = $this->lockFilePath.$lockIdentifier;
        if (!file_exists($lockFile)) {
            touch($lockFile);
        }

        return $lockFile;
    }

    /**
     * Gibt eine Fehlerseite bei einer Exception aus. Welche das ist wird über die ext conf exceptionPage
     * definiert. Dort kann entweder FILE:mysubsite/myerror.html angegeben werden oder
     * TYPOSCRIPT:typo3conf/ext/myext/static/mktools.setup.txt. Wie man das TS angibt lässt sich in
     * EXT:mktools/Configuration/TypoScript/errorhandling/setup.txt sehen.
     *
     * @param \Exception|\Throwable $exception
     */
    protected function echoExceptionInWebEnvironment($exception)
    {
        $this->sendStatusHeaders($exception);

        $this->writeLogEntries($exception, self::CONTEXT_WEB);

        if ($this->shouldExceptionBeDebugged()) {
            Debug::debug([
                'Exception! Mehr infos im devlog.',
            ], __METHOD__.' Line: '.__LINE__);
            Debug::debug([
                $exception,
            ], __METHOD__.' Line: '.__LINE__);
            // in BE context there is no need for a exception page and there might be redirects to the BE login.
            if (
                ($GLOBALS['TYPO3_REQUEST'] ?? null)
                && ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()
            ) {
                exit;
            }
        }

        if ((!$exceptionPage = $this->getExceptionPage())
            || (!$absoluteExceptionPageUrl = Network::locationHeaderUrl($exceptionPage))
        ) {
            $this->logNoExceptionPageDefined();
        } else {
            $this->echoExceptionPageAndExit($absoluteExceptionPageUrl);
        }
    }

    /**
     * @return bool
     */
    protected function shouldExceptionBeDebugged()
    {
        return Network::isDevelopmentIp();
    }

    /**
     * @return string Datei, welche angezeigt werden soll
     */
    private function getExceptionPage()
    {
        $exceptionPageType = $this->getExceptionPageType();
        $fileLink = $this->getExceptionPageFileLink();
        $exceptionPage = '';

        if ('FILE' === $exceptionPageType) {
            $exceptionPage = $fileLink;
        } elseif ('TYPOSCRIPT' === $exceptionPageType) {
            $configurations = $this->getConfigurations($fileLink);
            $exceptionPage = $configurations->get('errorhandling.exceptionPage');
        } else {
            Logger::warn('unbekannter error page type "'.$exceptionPageType.'" (möglich: FILE, TYPOSCRIPT)', 'mktools');
        }

        return $exceptionPage;
    }

    /**
     * @return string entweder FILE oder TYPOSCRIPT
     */
    private function getExceptionPageType()
    {
        $exceptionPageConfigurationParts = $this->getExceptionPageExtensionConfiguration();

        return $exceptionPageConfigurationParts[0];
    }

    /**
     * @return string entweder link zu einem TS oder zu einer Seite
     */
    private function getExceptionPageFileLink()
    {
        $exceptionPageConfigurationParts = $this->getExceptionPageExtensionConfiguration();

        return $exceptionPageConfigurationParts[1] ?? '';
    }

    /**
     * @return array
     */
    private function getExceptionPageExtensionConfiguration()
    {
        if (!$this->exceptionPageExtensionConfiguration) {
            $exceptionPageConfiguration = Misc::getExceptionPage();
            $this->exceptionPageExtensionConfiguration = explode(':', $exceptionPageConfiguration);
        }

        return $this->exceptionPageExtensionConfiguration;
    }

    /**
     * @param string $additionalPath
     *
     * @return Processor
     */
    private function getConfigurations($additionalPath = '')
    {
        if (null === $this->configurations) {
            $staticPath = 'EXT:mktools/Configuration/TypoScript/errorhandling/setup.txt';
            $this->configurations = \DMK\Mktools\Utility\Misc::getConfigurations($staticPath, $additionalPath);
        }

        return $this->configurations;
    }

    protected function logNoExceptionPageDefined()
    {
        Logger::warn('keine Fehlerseite definiert', 'mktools');
    }

    /**
     * @param string $absoluteExceptionPageUrl
     */
    protected function echoExceptionPageAndExit($absoluteExceptionPageUrl)
    {
        // wenn wir schon auf der Fehlerseite sind, dann holen wir nicht nochmal
        // die Fehlerseite falls auf dieser der Fehler auch auftritt. Sonst laufen
        // wir in einen infinite loop
        if (GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL') != $absoluteExceptionPageUrl) {
            echo Network::getURL($absoluteExceptionPageUrl);
        }
        exit(1);
    }

    /**
     * Methode ist in TYPO3 4.5.x noch nicht vorhanden. Daher selbst eingefügt.
     *
     * @param \Throwable|\Exception $exception
     */
    protected function sendStatusHeadersEnvironment($exception)
    {
        @parent::sendStatusHeaders($exception);
    }
}
