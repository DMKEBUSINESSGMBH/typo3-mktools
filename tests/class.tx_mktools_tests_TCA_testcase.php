<?php
/**
 * 	@package TYPO3
 *  @subpackage tx_mktools
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@dmk-ebusiness.de>
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
 */

tx_rnbase::load('tx_mklib_tests_Util');
tx_rnbase::load('tx_mktools_util_miscTools');

/**
 * @author Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 */
class tx_mktools_tests_TCA_testcase extends tx_phpunit_testcase {

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp(){
		tx_mklib_tests_Util::storeExtConf('mktools');
	}

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	protected function tearDown(){
		tx_mklib_tests_Util::restoreExtConf('mktools');
	}

	/**
	 * Setzt die TCA zurück und lädt die ext_tables.php erneut
	 */
	private static function loadExtTables(){
		global $TCA;
		unset($TCA['tx_mktools_fixedpostvartypes']);
		$_EXTKEY = 'mktools';
		include(tx_rnbase_util_Extensions::extPath('mktools', 'ext_tables.php'));
	}

	/**
	 * @group unit
	 */
	public function testTcaForFixedPostVarTypesIsNotIncludedIfNotSet(){
		global $TCA;

		tx_mklib_tests_Util::setExtConfVar('tableFixedPostVarTypes', 0, 'mktools');
		self::loadExtTables();

		$tableFixedPostVarTypes = tx_mktools_util_miscTools::loadFixedPostVarTypesTable();

		$this->assertEquals(
			0, intval($tableFixedPostVarTypes),
			'Die Extension Konfiguration tableFixedPostVarTypes ist falsch gesetzt.'
		);
		$this->assertFalse(
			array_key_exists('tx_mktools_fixedpostvartypes',$TCA),
			'Die TCA für die tx_mktools_fixedpostvartypes Tabelle wurde geladen.'
		);
	}

	/**
	 * @group unit
	 */
	public function testTcaForFixedPostVarTypesIsIncludedIfSet(){
		global $TCA;

		tx_mklib_tests_Util::setExtConfVar('tableFixedPostVarTypes', 1, 'mktools');
		self::loadExtTables();

		$tableFixedPostVarTypes = tx_mktools_util_miscTools::loadFixedPostVarTypesTable();

		$this->assertEquals(
			1, intval($tableFixedPostVarTypes),
			'Die Extension Konfiguration tableWordlist ist falsch gesetzt'
		);
		$this->assertTrue(
			array_key_exists('tx_mktools_fixedpostvartypes',$TCA),
			'Die TCA für die tx_mktools_fixedpostvartypes Tabelle wurde nicht geladen.'
		);
		$this->assertTrue(
			array_key_exists('ctrl',$TCA['tx_mktools_fixedpostvartypes']),
			'Die TCA für die tx_mktools_fixedpostvartypes Tabelle wurde nicht richtig geladen.'
		);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_TCA_testcase.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_TCA_testcase.php']);
}
