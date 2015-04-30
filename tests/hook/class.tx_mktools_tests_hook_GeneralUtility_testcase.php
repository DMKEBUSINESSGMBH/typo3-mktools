<?php
/*  **********************************************************************  **
 *  Copyright notice
 *
 *  (c) 2015 DMk E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('tx_mklib_tests_Util');
tx_rnbase::load('tx_mktools_hook_GeneralUtility');

/**
 *
 * tx_mktools_tests_hook_GeneralUtility_testcase
 *
 * @package 		TYPO3
 * @subpackage	 	mktools
 * @author 			Hannes Bochmann <dev@dmk-ebusiness.de>
 * @license 		http://www.gnu.org/licenses/lgpl.html
 * 					GNU Lesser General Public License, version 3 or later
 */
class tx_mktools_tests_hook_GeneralUtility_testcase extends tx_rnbase_tests_BaseTestCase {

	/**
	 * @var string
	 */
	private $systemLogConfigurationBackup;

	/**
	 * @var string
	 */
	private $hooksConfigurationBackup;

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp() {
		tx_mklib_tests_Util::storeExtConf('mktools');
		$this->systemLogConfigurationBackup =
			$GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'];

		$this->hooksConfigurationBackup =
			$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog'];
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog'] = array();
	}

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	protected function tearDown() {
		tx_mklib_tests_Util::restoreExtConf('mktools');
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] =
			$this->systemLogConfigurationBackup;

		$systemLogConfigurationBackup = new ReflectionProperty(
			'tx_mktools_hook_GeneralUtility', 'systemLogConfigurationBackup'
		);
		$systemLogConfigurationBackup->setAccessible(TRUE);
		$systemLogConfigurationBackup->setValue(NULL, '');

		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog'] =
			$this->hooksConfigurationBackup;
	}

	/**
	 * @group unit
	 */
	public function testHookIsNotRegisteredIfNoSystemLogLockThresholdIsConfigured() {
		tx_mklib_tests_Util::setExtConfVar('systemLogLockThreshold', 0, 'mktools');

		require t3lib_extMgm::extPath('mktools', 'ext_localconf.php');

		$hookFound = FALSE;
		foreach ((array)$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog'] as $systemLogHook) {
			if (strpos(
					$systemLogHook,
					'EXT:mktools/hook/class.tx_mktools_hook_GeneralUtility.php:tx_mktools_hook_GeneralUtility->preventSystemLogFlood'
				) !== FALSE
			) {
				$hookFound = TRUE;
			}
		}
		$this->assertFalse(
			$hookFound,
			'Hook doch registriert'
		);
	}

	/**
	 * @group unit
	 */
	public function testHookIsRegisteredIfSystemLogLockThresholdIsConfigured() {
		tx_mklib_tests_Util::setExtConfVar('systemLogLockThreshold', 123, 'mktools');

		require t3lib_extMgm::extPath('mktools', 'ext_localconf.php');

		$hookFound = FALSE;
		foreach ((array)$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog'] as $systemLogHook) {
			if (strpos(
				$systemLogHook,
				'EXT:mktools/hook/class.tx_mktools_hook_GeneralUtility.php:tx_mktools_hook_GeneralUtility->preventSystemLogFlood'
				) !== FALSE
			) {
				$hookFound = TRUE;
			}
		}
		$this->assertTrue(
			$hookFound,
			'Hook doch registriert'
		);
	}

	/**
	 * @group unit
	 */
	public function testPreventSystemLogFloodStoresBackupOfSystemLogConfigurationIfNoneIsSet() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] = 'someSystemLogDaemons';

		$systemLogConfigurationBackup = new ReflectionProperty(
			'tx_mktools_hook_GeneralUtility', 'systemLogConfigurationBackup'
		);
		$systemLogConfigurationBackup->setAccessible(TRUE);
		$this->assertEquals(
			'', $systemLogConfigurationBackup->getValue(),
			'zu Beginn doch eine Konfiguration gespeichert'
		);

		tx_mktools_hook_GeneralUtility::preventSystemLogFlood(array());

		$this->assertEquals(
			'someSystemLogDaemons', $systemLogConfigurationBackup->getValue(),
			'Konfiguration nicht gespeichert'
		);
	}

	/**
	 * @group unit
	 */
	public function testPreventSystemLogFloodStoresBackupOfSystemLogConfigurationNotIfAlreadySetButWritesBackupBackToGlobals() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] = 'someSystemLogDaemons';

		$systemLogConfigurationBackup = new ReflectionProperty(
			'tx_mktools_hook_GeneralUtility', 'systemLogConfigurationBackup'
		);
		$systemLogConfigurationBackup->setAccessible(TRUE);
		$systemLogConfigurationBackup->setValue(NULL, 'otherSystemLogDaemons');

		$lockUtility = $this->getMock(
				'tx_rnbase_util_Lock', array('isLocked', 'lockProcess'),
				array(), '', FALSE
		);

		$lockUtility->expects($this->once())
			->method('isLocked')
			->will($this->returnValue(FALSE));

		$hook = $this->getMockClass(
			'tx_mktools_hook_GeneralUtility', array('getLockUtility')
		);
		$hook::staticExpects($this->once())
			->method('getLockUtility')
			->will($this->returnValue($lockUtility));

		$hook::preventSystemLogFlood(array());

		$this->assertEquals(
			'otherSystemLogDaemons', $systemLogConfigurationBackup->getValue(),
			'Konfiguration doch gespeichert'
		);

		$this->assertEquals(
			'otherSystemLogDaemons', $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'],
			'Konfiguration nicht zurückgeschrieben'
		);
	}

	/**
	 * @group unit
	 */
	public function testGetLockUtility() {
		tx_mklib_tests_Util::setExtConfVar('systemLogLockThreshold', 123, 'mktools');

		$expectedLockUtility = tx_rnbase_util_Lock::getInstance(
			'15c79894401d2315b62f631234b9fb49', 123
		);
		$lockUtility = $this->callInaccessibleMethod(
			tx_rnbase::makeInstance('tx_mktools_hook_GeneralUtility'),
			'getLockUtility',
			array('msg' => 'fehler', 'extKey' => 'mktools', 'severity' => 2)
		);
		$this->assertEquals($expectedLockUtility, $lockUtility);
	}

	/**
	 * @group unit
	 */
	public function testPreventSystemLogFloodEmptiesSystemLogConfigurationIsLocked() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] = 'someSystemLogDaemons';
		$parameters = array('someParameters');
		$lockUtility = $this->getMock(
			'tx_rnbase_util_Lock', array('isLocked', 'lockProcess'),
			array(), '', FALSE
		);

		$lockUtility->expects($this->once())
			->method('isLocked')
			->will($this->returnValue(TRUE));

		$lockUtility->expects($this->never())
			->method('lockProcess');

		$hook = $this->getMockClass(
			'tx_mktools_hook_GeneralUtility', array('getLockUtility')
		);
		$hook::staticExpects($this->once())
			->method('getLockUtility')
			->with($parameters)
			->will($this->returnValue($lockUtility));

		$hook::preventSystemLogFlood($parameters);

		$this->assertEquals(
			'', $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'],
			'Konfiguration nicht geleert'
		);
	}

	/**
	 * @group unit
	 */
	public function testPreventSystemLogFloodEmptiesSystemLogConfigurationNotIfNotIsLocked() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] = 'someSystemLogDaemons';
		$parameters = array('someParameters');
		$lockUtility = $this->getMock(
				'tx_rnbase_util_Lock', array('isLocked', 'lockProcess'),
				array(), '', FALSE
		);

		$lockUtility->expects($this->once())
			->method('isLocked')
			->will($this->returnValue(FALSE));

		$lockUtility->expects($this->once())
			->method('lockProcess');

		$hook = $this->getMockClass(
			'tx_mktools_hook_GeneralUtility', array('getLockUtility')
		);
		$hook::staticExpects($this->once())
			->method('getLockUtility')
			->with($parameters)
			->will($this->returnValue($lockUtility));

		$hook::preventSystemLogFlood($parameters);

		$this->assertEquals(
			'someSystemLogDaemons', $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'],
			'Konfiguration doch geleert'
		);
	}
}