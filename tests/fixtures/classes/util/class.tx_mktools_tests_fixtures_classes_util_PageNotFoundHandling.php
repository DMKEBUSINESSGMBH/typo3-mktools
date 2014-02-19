<?php
/*  **********************************************************************  **
 *  Copyright notice
 *
 *  (c) 2012 das MedienKombinat GmbH <kontakt@das-medienkombinat.de>
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
require_once(t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php'));
tx_rnbase::load('tx_mktools_util_PageNotFoundHandling');
/**
 * Erweitert die util klasse mit test methoden
 * @package tx_mktools
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
 */
class tx_mktools_tests_fixtures_classes_util_PageNotFoundHandling
	extends tx_mktools_util_PageNotFoundHandling
{

	/**
	 * ist nur wahr, wenn wir uns im unittest befinden, um die exits zu vermeiden.
	 * @var boolean
	 */
	private $isTest = false;

	/**
	 *
	 * @param tslib_fe $tsfe
	 * @return tx_mktools_util_PageNotFoundHandling
	 */
	public static function getInstance(tslib_fe $tsfe, $reason = '')
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
		$this->isTest = true;
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

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']
		['ext/mktools/tests/fixtures/classes/util/class.tx_mktools_tests_fixtures_classes_util_PageNotFoundHandling.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']
		['ext/mktools/tests/fixtures/classes/util/class.tx_mktools_tests_fixtures_classes_util_PageNotFoundHandling.php']);
}
