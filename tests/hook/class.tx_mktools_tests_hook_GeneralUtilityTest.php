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

use DMK\Mktools\Hook\GeneralUtilityHook;

/**
 * tx_mktools_tests_hook_GeneralUtilityTest.
 *
 * @author          Hannes Bochmann <dev@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_mktools_tests_hook_GeneralUtilityTest extends tx_rnbase_tests_BaseTestCase
{
    /**
     * @var string
     */
    private $systemLogConfigurationBackup;

    /**
     * @var string
     */
    private $hooksConfigurationBackup;

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        \DMK\Mklib\Utility\Tests::storeExtConf('mktools');
        $this->systemLogConfigurationBackup =
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'];

        $this->hooksConfigurationBackup =
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog'];
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog'] = [];
    }

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        \DMK\Mklib\Utility\Tests::restoreExtConf('mktools');
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] =
            $this->systemLogConfigurationBackup;

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog'] =
            $this->hooksConfigurationBackup;
    }

    /**
     * @group unit
     */
    public function testHookIsNotRegisteredIfNoSystemLogLockThresholdIsConfigured()
    {
        self::markTestSkipped('Problem with type3 9.5 config');

        \DMK\Mklib\Utility\Tests::setExtConfVar('systemLogLockThreshold', 0, 'mktools');

        require tx_rnbase_util_Extensions::extPath('mktools', 'ext_localconf.php');

        $hookFound = false;
        foreach ((array) $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog'] as $systemLogHook) {
            if (false !== strpos(
                $systemLogHook,
                'tx_mktools_hook_GeneralUtility->preventSystemLogFlood'
            )
            ) {
                $hookFound = true;
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
    public function testHookIsRegisteredIfSystemLogLockThresholdIsConfigured()
    {
        self::markTestSkipped('Problem with type3 9.5 config');

        \DMK\Mklib\Utility\Tests::setExtConfVar('systemLogLockThreshold', 123, 'mktools');

        require tx_rnbase_util_Extensions::extPath('mktools', 'ext_localconf.php');

        $hookFound = false;
        foreach ((array) $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog'] as $systemLogHook) {
            if (false !== strpos(
                $systemLogHook,
                'tx_mktools_hook_GeneralUtility->preventSystemLogFlood'
            )
            ) {
                $hookFound = true;
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
    public function testPreventSystemLogFloodStoresBackupOfSystemLogConfigurationIfNoneIsSet()
    {
        self::markTestIncomplete(
            'This test has to be refactored.'
        );

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] = 'someSystemLogDaemons';

        $systemLogConfigurationBackup = new ReflectionProperty(
            GeneralUtilityHook::class,
            'systemLogConfigurationBackup'
        );
        $systemLogConfigurationBackup->setAccessible(true);
        $this->assertEquals(
            '',
            $systemLogConfigurationBackup->getValue(tx_rnbase::makeInstance(GeneralUtilityHook::class)),
            'zu Beginn doch eine Konfiguration gespeichert'
        );

        $hook = tx_rnbase::makeInstance(GeneralUtilityHook::class);
        $hook->preventSystemLogFlood(array());

        $this->assertEquals(
            'someSystemLogDaemons',
            $systemLogConfigurationBackup->getValue($hook),
            'Konfiguration nicht gespeichert'
        );
    }

    /**
     * @group unit
     */
    public function testPreventSystemLogFloodStoresBackupOfSystemLogConfigurationNotIfAlreadySetButWritesBackupBackToGlobals()
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] = 'someSystemLogDaemons';
        $hook = $this->getMock(
            GeneralUtilityHook::class,
            array('getLockUtility')
        );

        $systemLogConfigurationBackup = new ReflectionProperty(
            GeneralUtilityHook::class,
            'systemLogConfigurationBackup'
        );
        $systemLogConfigurationBackup->setAccessible(true);
        $systemLogConfigurationBackup->setValue($hook, 'otherSystemLogDaemons');

        $lockUtility = $this->getMock(
            'tx_rnbase_util_Lock',
            ['isLocked', 'lockProcess'],
            [],
            '',
            false
        );

        $lockUtility->expects($this->once())
            ->method('isLocked')
            ->will($this->returnValue(false));

        $hook->expects($this->once())
            ->method('getLockUtility')
            ->will($this->returnValue($lockUtility));

        $hook->preventSystemLogFlood([]);

        $this->assertEquals(
            'otherSystemLogDaemons',
            $systemLogConfigurationBackup->getValue($hook),
            'Konfiguration doch gespeichert'
        );

        $this->assertEquals(
            'otherSystemLogDaemons',
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'],
            'Konfiguration nicht zurückgeschrieben'
        );
    }

    /**
     * @group unit
     */
    public function testGetLockUtility()
    {
        self::markTestSkipped('Problem with type3 9.5 config');

        \DMK\Mklib\Utility\Tests::setExtConfVar('systemLogLockThreshold', 123, 'mktools');

        $expectedLockUtility = tx_rnbase_util_Lock::getInstance(
            '15c79894401d2315b62f631234b9fb49',
            123
        );
        $lockUtility = $this->callInaccessibleMethod(
            tx_rnbase::makeInstance(GeneralUtilityHook::class),
            'getLockUtility',
            ['msg' => 'fehler', 'extKey' => 'mktools', 'severity' => 2]
        );
        $this->assertEquals($expectedLockUtility, $lockUtility);
    }

    /**
     * @group unit
     */
    public function testPreventSystemLogFloodEmptiesSystemLogConfigurationIsLocked()
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] = 'someSystemLogDaemons';
        $parameters = ['someParameters'];
        $lockUtility = $this->getMock(
            'tx_rnbase_util_Lock',
            ['isLocked', 'lockProcess'],
            [],
            '',
            false
        );

        $lockUtility->expects($this->once())
            ->method('isLocked')
            ->will($this->returnValue(true));

        $lockUtility->expects($this->never())
            ->method('lockProcess');

        $hook = $this->getMock(
            GeneralUtilityHook::class,
            array('getLockUtility')
        );
        $hook->expects($this->once())
            ->method('getLockUtility')
            ->with($parameters)
            ->will($this->returnValue($lockUtility));

        $hook->preventSystemLogFlood($parameters);

        $this->assertEquals(
            '',
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'],
            'Konfiguration nicht geleert'
        );
    }

    /**
     * @group unit
     */
    public function testPreventSystemLogFloodEmptiesSystemLogConfigurationNotIfNotIsLocked()
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] = 'someSystemLogDaemons';
        $parameters = ['someParameters'];
        $lockUtility = $this->getMock(
            'tx_rnbase_util_Lock',
            ['isLocked', 'lockProcess'],
            [],
            '',
            false
        );

        $lockUtility->expects($this->once())
            ->method('isLocked')
            ->will($this->returnValue(false));

        $lockUtility->expects($this->once())
            ->method('lockProcess');

        $hook = $this->getMock(
            GeneralUtilityHook::class,
            array('getLockUtility')
        );
        $hook->expects($this->once())
            ->method('getLockUtility')
            ->with($parameters)
            ->will($this->returnValue($lockUtility));

        $hook->preventSystemLogFlood($parameters);

        $this->assertEquals(
            'someSystemLogDaemons',
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'],
            'Konfiguration doch geleert'
        );
    }
}
