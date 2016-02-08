<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 *  All rights reserved
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

tx_rnbase::load('Tx_Rnbase_Error_ProductionExceptionHandler');
tx_rnbase::load('tx_rnbase_util_Network');

/**
 * @author Hannes Bochmann
 */
class tx_mktools_util_ExceptionHandler extends Tx_Rnbase_Error_ProductionExceptionHandler {

	/**
	 * @var tx_rnbase_configurations
	 */
	private $configurations;

	/**
	 * @var array
	 */
	private $exceptionPageExtensionConfiguration = array();

	/**
	 * (non-PHPdoc)
	 * @see Tx_Rnbase_Error_ProductionExceptionHandler::writeLogEntries()
	 */
	protected function writeLogEntries(Exception $exception, $context) {
		//tx_mktools_util_ErrorException wird nur von
		//tx_mktools_util_ErrorHandler::handleError geworfen und wurde schon geloggt
		if (
			(!$exception instanceof tx_mktools_util_ErrorException) &&
			$this->lockAcquired($exception, $context)
		) {
			$this->writeLogEntriesByParent($exception, $context);
		}
	}

	/**
	 * damit wir mocken können
	 *
	 * (non-PHPdoc)
	 * @see Tx_Rnbase_Error_ProductionExceptionHandler::writeLogEntries()
	 */
	protected function writeLogEntriesByParent(Exception $exception, $context) {
		//warnungen beim Logging interessieren uns nicht. Ohne @ führt dies dazu dass
		//die Warnung beim Logging festgehalten wird, nicht aber die eigentliche
		//Meldung, wenn die Warnung vor dem Schreiben des Logs auftritt
		@parent::writeLogEntries($exception, $context);
	}

	/**
	 * @param Exception $exception
	 * @param string $context
	 *
	 * @return boolean
	 */
	protected function lockAcquired(Exception $exception, $context) {
		if (!is_dir(PATH_site.'typo3temp/mktools/locks/')) {
			tx_rnbase_util_Files::mkdir_deep(PATH_site . 'typo3temp/', 'mktools/locks');
		}

		$lockFile = $this->getLockFileByExceptionAndContext($exception, $context);

		$lastCall = intval(trim(file_get_contents($lockFile)));
		if ($lastCall > (time() - 60)) {
			return FALSE; // Only logging once a minute per error
		}

		file_put_contents($lockFile, time()); // refresh lock

		return TRUE;
	}

	/**
	 * @param Exception $exception
	 * @param unknown_type $context
	 *
	 * @return string
	 */
	protected function getLockFileByExceptionAndContext(Exception $exception, $context) {
		$lockFileName = md5(
			$exception->getCode() . $exception->getMessage() .
			$exception->getPrevious() . $context
		);

		$lockFilePath = PATH_site . 'typo3temp/mktools/locks/';
		if (!is_dir($lockFilePath)) {
			tx_rnbase_util_Files::mkdir_deep($lockFilePath);
		}

		$lockFile = $lockFilePath . $lockFileName . '.txt';
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
	 * @param Exception $exception The exception
	 * @return void
	 */
	public function echoExceptionWeb(Exception $exception) {
		$this->sendStatusHeaders($exception);

		$this->writeLogEntries($exception, self::CONTEXT_WEB);

		if ($this->shouldExceptionBeDebugged()) {
			tx_rnbase::load('tx_rnbase_util_Debug');
			tx_rnbase_util_Debug::debug(array(
				'Exception! Mehr infos im devlog.'
			),__METHOD__.' Line: '.__LINE__);
			tx_rnbase_util_Debug::debug(array(
				$exception
			),__METHOD__.' Line: '.__LINE__);
		}

		if(
			(!$exceptionPage = $this->getExceptionPage()) ||
			(!$absoluteExceptionPageUrl = tx_rnbase_util_Network::locationHeaderUrl($exceptionPage))
		) {
			$this->logNoExceptionPageDefined();
			return;
		} else {
			$this->echoExceptionPageAndExit($absoluteExceptionPageUrl);
		}
	}

	/**
	 * @return boolean
	 */
	protected function shouldExceptionBeDebugged() {
		return 	tx_rnbase_util_Network::isDevelopmentIp();
	}

	/**
	 * @return string Datei, welche angezeigt werden soll
	 */
	private function getExceptionPage() {
		$exceptionPageType = $this->getExceptionPageType();
		$fileLink = $this->getExceptionPageFileLink();
		$exceptionPage = '';

		if ($exceptionPageType == 'FILE') {
			$exceptionPage = $fileLink;
		} elseif($exceptionPageType == 'TYPOSCRIPT') {
			$confirgurations = $this->getConfigurations($fileLink);
			$exceptionPage = $confirgurations->get('errorhandling.exceptionPage');
		} else {
			tx_rnbase::load('tx_rnbase_util_Logger');
			tx_rnbase_util_Logger::warn('unbekannter error page type "' . $errorHandlingType . '" (möglich: FILE, TYPOSCRIPT)', 'mktools');
		}

		return $exceptionPage;
	}

	/**
	 * @return string entweder FILE oder TYPOSCRIPT
	 */
	private function getExceptionPageType() {
		$exceptionPageConfigurationParts = $this->getExceptionPageExtensionConfiguration();
		return $exceptionPageConfigurationParts[0];
	}

	/**
	 * @return string entweder link zu einem TS oder zu einer Seite
	 */
	private function getExceptionPageFileLink() {
		$exceptionPageConfigurationParts = $this->getExceptionPageExtensionConfiguration();
		return $exceptionPageConfigurationParts[1];
	}

	/**
	 * @return array
	 */
	private function getExceptionPageExtensionConfiguration() {
		if (!$this->exceptionPageExtensionConfiguration) {
			tx_rnbase::load('tx_mktools_util_miscTools');
			$exceptionPageConfiguration = tx_mktools_util_miscTools::getExceptionPage();
			$this->exceptionPageExtensionConfiguration = explode(':', $exceptionPageConfiguration);
		}

		return $this->exceptionPageExtensionConfiguration;
	}

	/**
	 * @param string $additionalPath
	 * @return 	tx_rnbase_configurations
	 */
	private function getConfigurations($additionalPath=''){
		if (is_null($this->configurations)) {
			$miscTools = tx_rnbase::makeInstance('tx_mktools_util_miscTools');
			$staticPath = 'EXT:mktools/Configuration/TypoScript/errorhandling/setup.txt';
			$this->configurations = $miscTools->getConfigurations($staticPath, $additionalPath);
		}
		return $this->configurations;
	}

	/**
	 * @return void
	 */
	protected function logNoExceptionPageDefined() {
		tx_rnbase::load('tx_rnbase_util_Logger');
		tx_rnbase_util_Logger::warn('keine Fehlerseite definiert', 'mktools');
	}

	/**
	 * @param string $absoluteExceptionPageUrl
	 *
	 * @return void
	 */
	protected function echoExceptionPageAndExit($absoluteExceptionPageUrl) {
		// wenn wir schon auf der Fehlerseite sind, dann holen wir nicht nochmal
		// die Fehlerseite falls auf dieser der Fehler auch auftritt. Sonst laufen
		// wir in einen infinite loop
		if(tx_rnbase_util_Misc::getIndpEnv('TYPO3_REQUEST_URL') != $absoluteExceptionPageUrl) {
			echo tx_rnbase_util_Network::getURL($absoluteExceptionPageUrl,0,FALSE, $report);
		}
		exit(1);
	}

	/**
	 * Methode ist in TYPO3 4.5.x noch nicht vorhanden. Daher selbst eingefügt.
	 *
	 * @param Exception $exception
	 * @return void
	 */
	protected function sendStatusHeaders(Exception $exception) {
		tx_rnbase::load('tx_rnbase_util_TYPO3');
		if (tx_rnbase_util_TYPO3::isTYPO46OrHigher()) {
			@parent::sendStatusHeaders($exception);
		} else {
			if (method_exists($exception, 'getStatusHeaders')) {
				$headers = $exception->getStatusHeaders();
			} else {
				$httpUtilityClass = tx_rnbase_util_Typo3Classes::getHttpUtilityClass();
				$headers = array($httpUtilityClass::HTTP_STATUS_500);
			}
			if (!headers_sent()) {
				foreach($headers as $header) {
					header($header);
				}
			}
		}
	}
}