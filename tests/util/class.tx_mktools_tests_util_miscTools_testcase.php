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
require_once(t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php'));
tx_rnbase::load('Tx_Phpunit_TestCase');
tx_rnbase::load('tx_mktools_util_miscTools');

/**
 * @package TYPO3
 * @author Hannes Bochmann
 */
class tx_mktools_tests_util_miscTools_testcase extends Tx_Phpunit_TestCase {

	/**
	 * @var string
	 */
	private $defaultPageTsConfig;

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp() {
		$this->defaultPageTsConfig = $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultPageTSconfig'];
		tx_mklib_tests_Util::storeExtConf('mktools');
	}

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	protected function tearDown() {
		$GLOBALS['TYPO3_CONF_VARS']['BE']['defaultPageTSconfig'] = $this->defaultPageTsConfig;
		tx_mklib_tests_Util::restoreExtConf('mktools');
	}

	/**
	 * @group unit
	 */
	public function testGetConfigurationsLoadsConfigCorrect() {
		$configurations = tx_mktools_util_miscTools::getConfigurations(
			'EXT:mktools/tests/fixtures/typoscript/miscTools1.txt'
		);

		$this->assertEquals(
			'config', $configurations->get('errorhandling.exceptionPage'),
			'Konfiguration nicht korrekt geladen'
		);
	}

	/**
	 * @group unit
	 */
	public function testGetConfigurationsPrefersPluginConfigurationOverConfigConfiguration() {
		$configurations = tx_mktools_util_miscTools::getConfigurations(
			'EXT:mktools/Configuration/TypoScript/errorhandling/setup.txt',
			'EXT:mktools/tests/fixtures/typoscript/miscTools2.txt'
		);

		$this->assertEquals(
			'plugin', $configurations->get('errorhandling.exceptionPage'),
			'plugin Konfiguration nicht bevorzugt'
		);
	}

	/**
	 * @group unit
	 */
	public function testGetRealUrlConfigurationTemplateWithAbsolutePath() {
		tx_mklib_tests_Util::setExtConfVar(
			'realUrlConfigurationTemplate',
			t3lib_extMgm::extPath('mktools') . 'tests/fixtures/realUrlConfigTemplate.php',
			'mktools'
		);

		$this->assertEquals(
			t3lib_extMgm::extPath('mktools') . 'tests/fixtures/realUrlConfigTemplate.php',
			tx_mktools_util_miscTools::getRealUrlConfigurationTemplate(),
			'realUrlConfigurationTemplate nicht absolut'
		);
	}

	/**
	 * @group unit
	 */
	public function testGetRealUrlConfigurationTemplateWithRelativePath() {
		tx_mklib_tests_Util::setExtConfVar(
			'realUrlConfigurationTemplate',
			'typo3conf/ext/mktools/tests/fixtures/realUrlConfigTemplate.php',
			'mktools'
		);

		$this->assertEquals(
			t3lib_extMgm::extPath('mktools') . 'tests/fixtures/realUrlConfigTemplate.php',
			tx_mktools_util_miscTools::getRealUrlConfigurationTemplate(),
			'realUrlConfigurationTemplate nicht absolut'
		);
	}

	/**
	 * @group unit
	 */
	public function testGetRealUrlConfigurationTemplateWithTypo3StylePath() {
		tx_mklib_tests_Util::setExtConfVar(
			'realUrlConfigurationTemplate',
			'EXT:mktools/tests/fixtures/realUrlConfigTemplate.php',
			'mktools'
		);

		$this->assertEquals(
			t3lib_extMgm::extPath('mktools') . 'tests/fixtures/realUrlConfigTemplate.php',
			tx_mktools_util_miscTools::getRealUrlConfigurationTemplate(),
			'realUrlConfigurationTemplate nicht absolut'
		);
	}

	/**
	 * @group unit
	 */
	public function testGetRealUrlConfigurationFileWithAbsolutePath() {
		tx_mklib_tests_Util::setExtConfVar(
			'realUrlConfigurationFile',
			t3lib_extMgm::extPath('mktools') . 'tests/fixtures/realUrlConfigTemplate.php',
			'mktools'
		);

		$this->assertEquals(
			t3lib_extMgm::extPath('mktools') . 'tests/fixtures/realUrlConfigTemplate.php',
			tx_mktools_util_miscTools::getRealUrlConfigurationFile(),
			'realUrlConfigurationFile nicht absolut'
		);
	}

	/**
	 * @group unit
	 */
	public function testGetRealUrlConfigurationFileWithRelativePath() {
		tx_mklib_tests_Util::setExtConfVar(
			'realUrlConfigurationFile',
			'typo3conf/realUrl.php',
			'mktools'
		);

		$this->assertEquals(
			PATH_site . 'typo3conf/realUrl.php',
			tx_mktools_util_miscTools::getRealUrlConfigurationFile(),
			'realUrlConfigurationFile nicht absolut'
		);
	}

	/**
	 * @group unit
	 */
	public function testGetRealUrlConfigurationFileWithTypo3StylePath() {
		tx_mklib_tests_Util::setExtConfVar(
			'realUrlConfigurationFile',
			'EXT:mktools/tests/fixtures/realUrlConfigTemplate.php',
			'mktools'
		);

		$this->assertEquals(
			t3lib_extMgm::extPath('mktools') . 'tests/fixtures/realUrlConfigTemplate.php',
			tx_mktools_util_miscTools::getRealUrlConfigurationFile(),
			'realUrlConfigurationFile nicht absolut'
		);
	}

	/**
	 * @group unit
	 */
	public function testGetTcaPostProcessingExtensions() {
		tx_mklib_tests_Util::setExtConfVar(
			'tcaPostProcessingExtensions',
			'ext1,ext2,',
			'mktools'
		);

		$this->assertEquals(
			array('ext1', 'ext2'),
			tx_mktools_util_miscTools::getTcaPostProcessingExtensions(),
			'extension array falsch'
		);
	}

	/**
	 * @group unit
	 */
	public function testGetSystemLogLockThreshold() {
		tx_mklib_tests_Util::setExtConfVar(
				'systemLogLockThreshold',
				123,
				'mktools'
		);

		$this->assertEquals(
			123,
			tx_mktools_util_miscTools::getSystemLogLockThreshold()
		);
	}
}