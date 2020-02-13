<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2012-2016 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Page not found handling.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class tx_mktools_util_PageNotFoundHandling
{
    /**
     * Current ts fe controller.
     *
     * @var Tx_Rnbase_Frontend_Controller_TypoScriptFrontendController
     */
    private $tsfe = null;
    private $reason = '';
    private $header = '';

    /**
     * The optionaly ts config for 404 handling.
     *
     * @var tx_rnbase_configurations
     */
    private $configurations = null;

    /**
     * Create an instance of this util.
     *
     * @param mixed|Tx_Rnbase_Frontend_Controller_TypoScriptFrontendController $tsfe
     * @param string                                                           $reason
     * @param string                                                           $header
     *
     * @return tx_mktools_util_PageNotFoundHandling
     */
    public static function getInstance($tsfe, $reason = '', $header = '')
    {
        // wir können leider nicht mit einem typehint arbeiten auf Grund
        // der verschiedenen Klassen abhängig von der TYPO3 Version
        $typoScriptFrontendControllerClass = tx_rnbase_util_Typo3Classes::getTypoScriptFrontendControllerClass();
        if (!($tsfe instanceof $typoScriptFrontendControllerClass)) {
            throw new InvalidArgumentException('The first parameter has to be a instance of "tslib_fe"!', intval(ERROR_CODE_MKTOOLS.'100'));
        }

        return new self($tsfe);
    }

    /**
     * Construct.
     *
     * @param mixed|Tx_Rnbase_Frontend_Controller_TypoScriptFrontendController $tsfe
     * @param string                                                           $reason
     * @param string                                                           $header
     *
     * @return tx_mktools_util_PageNotFoundHandling
     */
    public function __construct($tsfe, $reason = '', $header = '')
    {
        // wir können leider nicht mit einem typehint arbeiten auf Grund
        // der verschiedenen Klassen abhängig von der TYPO3 Version
        $typoScriptFrontendControllerClass = tx_rnbase_util_Typo3Classes::getTypoScriptFrontendControllerClass();
        if (!($tsfe instanceof $typoScriptFrontendControllerClass)) {
            throw new InvalidArgumentException('The first parameter has to be a instance of "tslib_fe"!', intval(ERROR_CODE_MKTOOLS.'100'));
        }
        $this->tsfe = $tsfe;
        $this->reason = $reason;
        $this->header = $header;

        tx_rnbase_util_Misc::callHook(
            'mktools',
            'general_hook_for_page_not_found_handling',
            [
                        'tsfe' => $this->tsfe,
                        'reason' => $this->reason,
                        'header' => $this->header,
                ]
        );
    }

    /**
     * Do the Magic and handle the 404.
     *
     * @param string $code Der Inhalt von TYPO3_CONF_VARS->FE->pageNotFound_handling
     *
     * @return bool
     */
    public function handlePageNotFound($code = '')
    {
        // keine mktools config, weiter machen!
        if (!tx_rnbase_util_Strings::isFirstPartOfStr($code, 'MKTOOLS_')) {
            return null;
        }

        $code = substr($code, strlen('MKTOOLS_'));
        $type = substr($code, 0, strpos($code, ':'));
        $data = substr($code, strlen($type) + 1);

        if ('TYPOSCRIPT' == $type) {
            if (!empty($data)) {
                $addTs = $data;
            }
        }

        // die Config initial anlegen!
        $configurations = $this->getConfigurations($addTs);

        // Auf zu ignorierende Fehlercodes prüfen.
        $ignorecodes = $configurations->get('pagenotfoundhandling.ignorecodes');
        if (tx_rnbase_util_Strings::inList($ignorecodes, $this->getTsFe()->pageNotFound)) {
            return;
        }

        // Type und data aus dem TS holen.
        if ('TYPOSCRIPT' == $type) {
            $type = $this->getTypeFromConfiguration();
            $data = $this->getDataFromConfiguration();
            $logPageNotFound = $this->getLogPageNotFoundFromConfiguration();
        }

        // Handling von mehrsprachigen 404 Seiten
        $languageCode = $this->getCurrentLanguage();
        if ($languageCode) {
            if ($this->getDataFromConfiguration($languageCode)) {
                $data = $this->getDataFromConfiguration($languageCode);
            }
        }

        if (empty($type) || empty($data)) {
            throw new InvalidArgumentException('Type or data missing! (MKTOOLS_[TYPE]:[DATA])', intval(ERROR_CODE_MKTOOLS.'110'));
        }

        if ($logPageNotFound) {
            $this->logPageNotFound($data, $type);
        }

        // Rekursion verhindern falls die 404 Seite selbst nicht gefunden werden konnte.
        if ($this->isRequestedPageAlready404Page($data)) {
            $this->setHeaderAndExit('Unerwarteter Fehler. Die 404 Seite wurde nicht gefunden.');
        }

        switch ($type) {
            case 'READFILE':
                $this->printContent($data);
                break;
            case 'REDIRECT':
                $this->redirectTo($data);
                break;
            default:
                throw new InvalidArgumentException('Unknown type "'.$type.'" found!', intval(ERROR_CODE_MKTOOLS.'110'));
        }
    }

    /**
     * Log the handling.
     *
     * @param mixed  $data
     * @param string $type
     */
    private function logPageNotFound($data, $type)
    {
        tx_rnbase_util_Logger::info(
            'Seite nicht gefunden',
            'mktools',
            [
                'reason' => $this->reason,
                'code' => $this->getTsFe()->pageNotFound,
                'REQUEST_URI' => tx_rnbase_util_Misc::getIndpEnv('REQUEST_URI'),
                'data' => $data,
                'type' => $type,
            ]
        );
    }

    /**
     * Um Rekursion zu verhindern.
     *
     * @param string $url The Url to check
     *
     * @return bool
     */
    protected function isRequestedPageAlready404Page($url)
    {
        return $url == tx_rnbase_util_Misc::getIndpEnv('REQUEST_URI');
    }

    /**
     * Get the content of the file or url and print to out.
     *
     * @param string $url The url or file with the content to print out
     */
    protected function printContent($url)
    {
        tx_rnbase_util_Misc::callHook(
            'mktools',
            'pagenotfoundhandling_beforePrintContent',
            [
                'url' => &$url,
            ]
        );

        $report = [];
        // wir versuchen erstmal den inhalt der URL zu holen
        $content = tx_rnbase_util_Network::getURL(
            $this->getFileAbsFileName($url), 0, false, $report
        );

        tx_rnbase_util_Misc::callHook(
            'mktools',
            'pagenotfoundhandling_afterGetContentByUrl',
            [
                'url' => &$url,
                'content' => &$content,
                'debug' => $report,
            ]
        );

        // wir liefern den 404 aus, ohne einen redirect!
        // damit bleibt auch die url die gleiche :)
        if ($content) {
            $content = str_replace(
                '###CURRENT_URL###',
                tx_rnbase_util_Misc::getIndpEnv('REQUEST_URI'),
                $content
            );
            $content = str_replace(
                '###REASON###',
                htmlspecialchars($this->reason),
                $content
            );
            $this->setHeaderAndExit($content);
            // wichtig für die testcases
            return null;
        }
        // else, wir leiten weiter.

        $this->redirectTo($url);

        // wichtig für die testcases
        return null;
    }

    /**
     * Performs an reditect.
     *
     * @param string $url The url to redirect to
     */
    protected function redirectTo($url)
    {
        $this->setHeaderAndExit(
            $this->getFileAbsFileName($url)
        );

        // wichtig für die testcases
        return null;
    }

    /**
     * Returns the current TS FE controller.
     *
     * @return Tx_Rnbase_Frontend_Controller_TypoScriptFrontendController
     */
    protected function getTsFe()
    {
        return $this->tsfe;
    }

    /**
     * The current ext ts conf.
     *
     * @param string $additionalPath Optionaly aditional ts file path
     *
     * @return tx_rnbase_configurations
     */
    protected function &getConfigurations($additionalPath = '')
    {
        if (is_null($this->configurations)) {
            /* @var $miscTools tx_mktools_util_miscTools */
            $miscTools = tx_rnbase::makeInstance('tx_mktools_util_miscTools');
            $staticPath = 'EXT:mktools/Configuration/TypoScript/pagenotfoundhandling/setup.txt';
            $this->configurations = $miscTools->getConfigurations($staticPath, $additionalPath);
        }

        return $this->configurations;
    }

    /**
     * Sets the header and stop code execution.
     *
     * @param string $contentOrUrl Content to print or url to redirect to
     */
    protected function setHeaderAndExit($contentOrUrl)
    {
        $httpStatus = $this->getHttpStatus();
        if ($this->isUri($contentOrUrl)) {
            $httpUtility = tx_rnbase_util_TYPO3::getHttpUtilityClass();
            $httpUtility::redirect($contentOrUrl, $httpStatus);
        }
        header($httpStatus);
        exit($contentOrUrl);
    }

    /**
     * Returns the absolute filename of a relative reference.
     *
     * @param string $filename The Path to the file
     *
     * @return string
     */
    private function getFileAbsFileName($filename)
    {
        $filename = trim($filename);

        if ('EXT:' === substr($filename, 0, 4)) {
            $filename = tx_rnbase_util_Files::getFileAbsFileName($filename);
        } else {
            $filename = tx_rnbase_util_Network::locationHeaderUrl($filename);
        }

        return $filename;
    }

    /**
     * Checks if the url is an uri.
     *
     * @param string $url The url to check
     *
     * @return bool
     */
    private function isUri($url)
    {
        return is_array($parsedUrl = parse_url($url)) && $parsedUrl['scheme'];
    }

    /**
     * The HTTP status to set for 404.
     *
     * @return string
     */
    protected function getHttpStatus()
    {
        $httpStatus = $this->header;
        if (empty($httpStatus)) {
            $httpStatus = $this->getHttpStatusFromConfiguration();
        }
        if (empty($httpStatus)) {
            $httpStatus = $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling_statheader'];
        }
        $httpUtility = tx_rnbase_util_TYPO3::getHttpUtilityClass();

        return  empty($httpStatus) ? $httpUtility::HTTP_STATUS_404 : $httpStatus;
    }

    /**
     * Reads the Type from TS.
     *
     * @return string
     */
    private function getTypeFromConfiguration()
    {
        return $this->getConfigurationKeyValueByPageNotFoundCode('type');
    }

    /**
     * Reads data from TS.
     *
     * @param string $languageCode An optionaly Language code
     *
     * @return string
     */
    private function getDataFromConfiguration($languageCode = false)
    {
        $typoScriptKey = $languageCode ? $languageCode.'.data' : 'data';

        return $this->getConfigurationKeyValueByPageNotFoundCode($typoScriptKey);
    }

    /**
     * Log 404 handling?
     *
     * @return bool
     */
    private function getLogPageNotFoundFromConfiguration()
    {
        return (bool) $this->getConfigurationKeyValueByPageNotFoundCode(
            'logPageNotFound'
        );
    }

    /**
     * HTTP status from Config.
     *
     * @return string
     */
    private function getHttpStatusFromConfiguration()
    {
        return $this->getConfigurationKeyValueByPageNotFoundCode('httpStatus');
    }

    /**
     * Entweder den default Wert oder den für den spezifischen Code
     * Beispiel Konfig:
     * config.tx_mktools.pagenotfoundhandling {
     *      ### default
     *      type = READFILE
     *      data = /404.
     *
     *      ### wenn der Nutzer keine Berechtigungen hat, dann soll er auf die Startseite umgeleitet werden
     *      pageNotFoundCodes {
     *          1 {
     *              type = REDIRECT
     *              data = /
     *              httpStatus...
     *              logPageNotFound...
     *          }
     *          2 {
     *              data = /
     *          }
     *      }
     *  }
     *
     * @param string $typoScriptKey ConfId
     *
     * @return string
     */
    private function getConfigurationKeyValueByPageNotFoundCode($typoScriptKey)
    {
        $pageNotFoundCode = $this->getTsFe()->pageNotFound;
        $value = $this->getConfigurations()->get(
            'pagenotfoundhandling.pageNotFoundCodes.'.$pageNotFoundCode.
            '.'.$typoScriptKey,
            true
        );

        if (!$value) {
            $value = $this->getConfigurations()->get('pagenotfoundhandling.'.$typoScriptKey, true);
        }

        return $value;
    }

    /**
     * Liefert Kürzel der aktuell gesetzten Sprache.
     * Bei aktivierten realurl kann diese nicht auf dem üblichen Weg ausgewertet
     * werden. Realurl kann die URL nicht auflösen, da es keine gültige Seite hat.
     * Demzufolge kann der L-Parameter nicht einfach z.B: über TS abgefragt werden.
     *
     * @return string With countrycode or NULL
     */
    private function getCurrentLanguage()
    {
        if (tx_rnbase_util_Extensions::isLoaded('realurl') && is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'])) {
            $realurlConf = array_shift($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']);
            if ($realurlConf &&
                is_array($realurlConf['preVars']) &&
                $realurlConf['pagePath']['languageGetVar']
            ) {
                // look for language configuration
                foreach ($realurlConf['preVars'] as $conf) {
                    if ($conf['GETvar'] == $realurlConf['pagePath']['languageGetVar']) {
                        foreach ($conf['valueMap'] as $countrycode => $value) {
                            // we expect a part like "/de/" in requested url
                            if ((
                                false !== strpos(
                                    tx_rnbase_util_Misc::getIndpEnv('TYPO3_REQUEST_URL'),
                                    '/'.$countrycode.'/'
                                )
                            )) {
                                return $countrycode;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Anpassung tslib_fe für 404.
     */
    public static function registerXclass()
    {
        // kann schon durch autoloading da sein aber auch eine andere Klasse sein
        // als die von mktools
        if (class_exists('ux_tslib_fe')) {
            $reflector = new ReflectionClass('ux_tslib_fe');
            $rPath = realpath($reflector->getFileName());
            $tPath = realpath(
                tx_rnbase_util_Extensions::extPath('mktools', '/xclasses/class.ux_tslib_fe.php')
            );
            // notice werfen wenn bisherige XClass nicht die von mktools ist
            if (false === strpos($rPath, $tPath)) {
                throw new LogicException('There allready exists an ux_tslib_fe XCLASS!'.' Remove the other XCLASS or the deacivate the page not found handling in mktools', intval(ERROR_CODE_MKTOOLS.'130'));
            }
            unset($reflector, $rPath, $tPath);
        } else {
            require_once tx_rnbase_util_Extensions::extPath('mktools').'xclasses/class.ux_tslib_fe.php';
        }

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController'] = [
            'className' => 'ux_tslib_fe',
        ];
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mktools/util/class.tx_mktools_util_PageNotFoundHandling.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mktools/util/class.tx_mktools_util_PageNotFoundHandling.php'];
}
