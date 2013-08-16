<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
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
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
require_once(PATH_t3lib . 'error/class.t3lib_error_abstractexceptionhandler.php');
require_once(PATH_t3lib . 'error/class.t3lib_error_productionexceptionhandler.php');

/**
 * @author Hannes Bochmann
 */
class tx_mktools_util_ExceptionHandler extends t3lib_error_ProductionExceptionHandler {

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
	 * @see t3lib_error_AbstractExceptionHandler::writeLogEntries()
	 */
	protected function writeLogEntries(Exception $exception, $context) {
		//tx_mktools_util_ErrorException wird nur von
		//tx_mktools_util_ErrorHandler::handleError geworfen und wurde schon geloggt
		if (!$exception instanceof tx_mktools_util_ErrorException) {
			$this->writeLogEntriesByParent($exception, $context);
		}
	}
	
	/**
	 * damit wir mocken können 
	 * 
	 * (non-PHPdoc)
	 * @see t3lib_error_AbstractExceptionHandler::writeLogEntries()
	 */
	protected function writeLogEntriesByParent(Exception $exception, $context) {
		parent::writeLogEntries($exception, $context);
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
			),__METHOD__.' Line: '.__LINE__); // @TODO: remove me
			tx_rnbase_util_Debug::debug(array(
				$exception
			),__METHOD__.' Line: '.__LINE__); // @TODO: remove me
		}

		if(
			(!$exceptionPage = $this->getExceptionPage()) ||
			(!$absoluteExceptionPageUrl = t3lib_div::locationHeaderUrl($exceptionPage))
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
		return defined('TYPO3_ERRORHANDLER_MODE') && TYPO3_ERRORHANDLER_MODE == 'debug';
	}

	/**
	 * @return string Datei, welche angezeigt werden soll
	 */
	private function getExceptionPage() {
		$exceptionPageType = $this->getExceptionPageType();
		$fileLink = $this->getExceptionPageFileLink();
		$exceptionPage = '';

		if($exceptionPageType == 'FILE') {
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
		if(!$this->exceptionPageExtensionConfiguration) {
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
		if(is_null($this->configurations)) {
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
		echo t3lib_div::getURL($absoluteExceptionPageUrl);
		exit(1);
	}
	
	/**
	 * Methode ist in TYPO3 4.5.x noch nicht vorhanden. Daher selbst eingefügt. 
	 * 
	 * Sends the HTTP Status 500 code, if $exception is *not* a t3lib_error_http_StatusException
	 * and headers are not sent, yet.
	 *
	 * @param Exception $exception
	 * @return void
	 */
	protected function sendStatusHeaders(Exception $exception) {
		if (method_exists(parent, 'sendStatusHeaders')) {
			parent::sendStatusHeaders($exception);
		} else {
			if (method_exists($exception, 'getStatusHeaders')) {
				$headers = $exception->getStatusHeaders();
			} else {
				$headers = array(t3lib_utility_Http::HTTP_STATUS_500);
			}
			if (!headers_sent()) {
				foreach($headers as $header) {
					header($header);
				}
			}
		}
	}
}