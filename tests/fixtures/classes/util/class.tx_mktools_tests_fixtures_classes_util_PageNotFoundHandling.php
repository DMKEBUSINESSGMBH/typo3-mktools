<?php
/*  **********************************************************************  **
 *  Copyright notice
 *
 *  (c) 2012 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
 *  ***********************************************************************  */

tx_rnbase::load('tx_mktools_util_PageNotFoundHandling');
/**
 * Erweitert die util klasse mit test methoden
 * @package TYPO3
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mktools_tests_fixtures_classes_util_PageNotFoundHandling
	extends tx_mktools_util_PageNotFoundHandling
{

	/**
	 * ist nur wahr, wenn wir uns im unittest befinden, um die exits zu vermeiden.
	 * @var boolean
	 */
	private $isTest = FALSE;

	/**
	 *
	 * @param Tx_Rnbase_Frontend_Controller_TypoScriptFrontendController $tsfe
	 * @return tx_mktools_util_PageNotFoundHandling
	 */
	public static function getInstance($tsfe, $reason = '')
	{
		return new self($tsfe, $reason);
	}

	/**
	 * enthält einen Wert zum auswerden in den tests
	 * @var array
	 */
	private $testValues = array();

	public function setTestMode()
	{
		$this->isTest = TRUE;
	}
	public function getTestValue()
	{
		return $this->testValues;
	}

	protected function setHeaderAndExit($contentOrUrl = '', $httpStatus = '')
	{
		$httpStatus = $this->getHttpStatus($httpStatus);
		if ($this->isTest) {
			$this->testValues['contentOrUrl'] = $contentOrUrl;
			$this->testValues['httpStatus'] = $httpStatus;
			return;
		}

		return parent::setHeaderAndExit($contentOrUrl, $httpStatus);
	}

}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']
		['ext/mktools/tests/fixtures/classes/util/class.tx_mktools_tests_fixtures_classes_util_PageNotFoundHandling.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']
		['ext/mktools/tests/fixtures/classes/util/class.tx_mktools_tests_fixtures_classes_util_PageNotFoundHandling.php']);
}
