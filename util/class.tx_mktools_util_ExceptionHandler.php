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
	private $errorPageExtensionConfiguration = array();

	/**
	 * Gibt eine Fehlerseite bei einer Exception aus. Welche das ist wird über die ext conf errorPage
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
				$exception
			),__METHOD__.' Line: '.__LINE__); // @TODO: remove me
		}

		if(
			(!$errorPage = $this->getErrorPage()) ||
			(!$absoluteErrorPageUrl = t3lib_div::locationHeaderUrl($errorPage))
		) {
			$this->logNoErrorPageDefined();
			return;
		} else {
			$this->echoErrorPageAndExit($absoluteErrorPageUrl);
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
	private function getErrorPage() {
		$errorPageType = $this->getErrorPageType();
		$fileLink = $this->getErrorPageFileLink();
		$errorPage = '';

		if($errorPageType == 'FILE') {
			$errorPage = $fileLink;
		} elseif($errorPageType == 'TYPOSCRIPT') {
			$confirgurations = $this->getConfirgurations($fileLink);
			$errorPage = $confirgurations->get('errorhandling.errorPage');
		} else {
			tx_rnbase::load('tx_rnbase_util_Logger');
			tx_rnbase_util_Logger::warn('unbekannter error page type "' . $errorHandlingType . '" (möglich: FILE, TYPOSCRIPT)', 'mktools');
		}

		return $errorPage;
	}

	/**
	 * @return string entweder FILE oder TYPOSCRIPT
	 */
	private function getErrorPageType() {
		$errorPageConfigurationParts = $this->getErrorPageExtensionConfiguration();
		return $errorPageConfigurationParts[0];
	}

	/**
	 * @return string entweder link zu einem TS oder zu einer Seite
	 */
	private function getErrorPageFileLink() {
		$errorPageConfigurationParts = $this->getErrorPageExtensionConfiguration();
		return $errorPageConfigurationParts[1];
	}

	/**
	 * @return array
	 */
	private function getErrorPageExtensionConfiguration() {
		if(!$this->errorPageExtensionConfiguration) {
			tx_rnbase::load('tx_mktools_util_miscTools');
			$errorPageConfiguration = tx_mktools_util_miscTools::getErrorPage();
			$this->errorPageExtensionConfiguration = explode(':', $errorPageConfiguration);
		}

		return $this->errorPageExtensionConfiguration;
	}

	/**
	 * @param string $additionalPath
	 * @return 	tx_rnbase_configurations
	 */
	private function getConfirgurations($additionalPath=''){
		if(is_null($this->configurations)) {
			$miscTools = tx_rnbase::makeInstance('tx_mktools_util_miscTools');
			$staticPath = 'EXT:mktools/Configuration/TypoScript/errorhandling/setup.txt';
			$this->configurations = $miscTools->getConfirgurations($staticPath, $additionalPath);
		}
		return $this->configurations;
	}

	/**
	 * @return void
	 */
	protected function logNoErrorPageDefined() {
		tx_rnbase::load('tx_rnbase_util_Logger');
		tx_rnbase_util_Logger::warn('keine Fehlerseite definiert', 'mktools');
	}

	/**
	 * @param string $absoluteErrorPageUrl
	 *
	 * @return void
	 */
	protected function echoErrorPageAndExit($absoluteErrorPageUrl) {
		echo t3lib_div::getURL($absoluteErrorPageUrl);
		exit;
	}
}