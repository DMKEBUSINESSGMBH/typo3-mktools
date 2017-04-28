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

tx_rnbase::load('tx_mklib_tests_Util');
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');

/**
 * @package TYPO3
 * @author  Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 */
class tx_mktools_tests_hook_extTables_PostProcessing_testcase extends tx_rnbase_tests_BaseTestCase
{

    /**
     *
     * @var array
     */
    private $hookBackup = array();

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->hookBackup = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing'];
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing'] = array();
        tx_mklib_tests_Util::storeExtConf('mktools');
    }

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing']
            = $this->hookBackup;
        tx_mklib_tests_Util::restoreExtConf('mktools');

        if (isset($GLOBALS['TCA']['tx_cal_event']['columns']['tx_mktools_fal_images'])) {
            unset($GLOBALS['TCA']['tx_cal_event']['columns']['tx_mktools_fal_images']);
        }

        if (isset($GLOBALS['TCA']['tt_news']['columns']['tx_mktools_fal_images'])) {
            unset($GLOBALS['TCA']['tt_news']['columns']['tx_mktools_fal_images']);
        }

        if (isset($GLOBALS['TCA']['tt_news']['columns']['tx_mktools_fal_media'])) {
            unset($GLOBALS['TCA']['tt_news']['columns']['tx_mktools_fal_media']);
        }
    }

    /**
     * @group unit
     */
    public function testHookIsNotRegisteredInTypo62IfNoTcaPostProcessingExtensionsConfigured()
    {
        if (!tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
            $this->markTestSkipped('geht nur ab TYPO3 6.2');
        }

        tx_mklib_tests_Util::setExtConfVar(
            'tcaPostProcessingExtensions',
            '',
            'mktools'
        );
        require tx_rnbase_util_Extensions::extPath('mktools', 'ext_localconf.php');

        $this->assertEmpty(
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing'],
            'hook doch registriert'
        );
    }

    /**
     * @group unit
     */
    public function testHookIsRegisteredInTypo62IfTcaPostProcessingExtensionsConfigured()
    {
        if (!tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
            $this->markTestSkipped('geht nur ab TYPO3 6.2');
        }

        tx_mklib_tests_Util::setExtConfVar(
            'tcaPostProcessingExtensions',
            'ext1',
            'mktools'
        );
        require tx_rnbase_util_Extensions::extPath('mktools', 'ext_localconf.php');

        $this->assertEquals(
            array('EXT:mktools/hook/extTables/class.tx_mktools_hook_extTables_PostProcessing.php:tx_mktools_hook_extTables_PostProcessing'),
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing'],
            'hook nicht registriert'
        );
    }

    /**
     * @group unit
     */
    public function testHookIsNotRegisteredInTypo45EvenIfTcaPostProcessingExtensionsConfigured()
    {
        if (tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
            $this->markTestSkipped('geht nur in TYPO3 4.5');
        }

        tx_mklib_tests_Util::setExtConfVar(
            'tcaPostProcessingExtensions',
            'ext1',
            'mktools'
        );
        require tx_rnbase_util_Extensions::extPath('mktools', 'ext_localconf.php');

        $this->assertEmpty(
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing'],
            'hook doch registriert'
        );
    }

    /**
     * @group unit
     */
    public function testProcessDataLoadsTcaOverridesOfMktoolsForCal()
    {
        if (!tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
            $this->markTestSkipped('geht nur in TYPO3 6.2');
        }
        if (!tx_rnbase_util_Extensions::isLoaded('cal')) {
            $this->markTestSkipped('cal Extension muss geladen sein');
        }

        tx_mklib_tests_Util::setExtConfVar(
            'tcaPostProcessingExtensions',
            'mktools',
            'mktools'
        );
        tx_mklib_tests_Util::setExtConfVar(
            'shouldFalImagesBeAddedToCalEvent',
            1,
            'mktools'
        );
        require tx_rnbase_util_Extensions::extPath('mktools', 'ext_localconf.php');

        $bootstrap = \TYPO3\CMS\Core\Core\Bootstrap::getInstance();

        $this->callInaccessibleMethod($bootstrap, 'runExtTablesPostProcessingHooks');

        $this->assertNotEmpty(
            $GLOBALS['TCA']['tx_cal_event']['columns']['tx_mktools_fal_images'],
            'fal images nicht vorhanden'
        );
    }

    /**
     * @group unit
     */
    public function testProcessDataLoadsTcaOverridesOfMktoolsForCalNotIfFalImagesForCalShouldNotBeIncluded()
    {
        if (!tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
            $this->markTestSkipped('geht nur in TYPO3 6.2');
        }
        if (!tx_rnbase_util_Extensions::isLoaded('cal')) {
            $this->markTestSkipped('cal Extension muss geladen sein');
        }

        tx_mklib_tests_Util::setExtConfVar(
            'tcaPostProcessingExtensions',
            'mktools',
            'mktools'
        );

        tx_mklib_tests_Util::setExtConfVar(
            'shouldFalImagesBeAddedToCalEvent',
            0,
            'mktools'
        );
        require tx_rnbase_util_Extensions::extPath('mktools', 'ext_localconf.php');

        $bootstrap = \TYPO3\CMS\Core\Core\Bootstrap::getInstance();

        $this->callInaccessibleMethod($bootstrap, 'runExtTablesPostProcessingHooks');

        $this->assertEmpty(
            $GLOBALS['TCA']['tx_cal_event']['columns']['tx_mktools_fal_images'],
            'fal images doch vorhanden'
        );
    }

    /**
     * @group unit
     */
    public function testProcessDataLoadsTcaOverridesOfMktoolsForTtNews()
    {
        if (!tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
            $this->markTestSkipped('geht nur in TYPO3 6.2');
        }
        if (!tx_rnbase_util_Extensions::isLoaded('tt_news')) {
            $this->markTestSkipped('tt_news Extension muss geladen sein');
        }

        tx_mklib_tests_Util::setExtConfVar(
            'tcaPostProcessingExtensions',
            'mktools',
            'mktools'
        );
        tx_mklib_tests_Util::setExtConfVar(
            'shouldFalImagesBeAddedToTtNews',
            1,
            'mktools'
        );
        require tx_rnbase_util_Extensions::extPath('mktools', 'ext_localconf.php');

        $bootstrap = \TYPO3\CMS\Core\Core\Bootstrap::getInstance();

        $this->callInaccessibleMethod($bootstrap, 'runExtTablesPostProcessingHooks');

        $this->assertNotEmpty(
            $GLOBALS['TCA']['tt_news']['columns']['tx_mktools_fal_images'],
            'fal images nicht vorhanden'
        );
        $this->assertNotEmpty(
            $GLOBALS['TCA']['tt_news']['columns']['tx_mktools_fal_media'],
            'fal media nicht vorhanden'
        );
    }

    /**
     * @group unit
     */
    public function testProcessDataLoadsTcaOverridesOfMktoolsForTtNewsNotIfFalImagesForTtNewsShouldNotBeIncluded()
    {
        if (!tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
            $this->markTestSkipped('geht nur in TYPO3 6.2');
        }
        if (!tx_rnbase_util_Extensions::isLoaded('tt_news')) {
            $this->markTestSkipped('tt_news Extension muss geladen sein');
        }

        tx_mklib_tests_Util::setExtConfVar(
            'tcaPostProcessingExtensions',
            'mktools',
            'mktools'
        );

        tx_mklib_tests_Util::setExtConfVar(
            'shouldFalImagesBeAddedToTtNews',
            0,
            'mktools'
        );
        require tx_rnbase_util_Extensions::extPath('mktools', 'ext_localconf.php');

        $bootstrap = \TYPO3\CMS\Core\Core\Bootstrap::getInstance();

        $this->callInaccessibleMethod($bootstrap, 'runExtTablesPostProcessingHooks');

        $this->assertEmpty(
            $GLOBALS['TCA']['tt_news']['columns']['tx_mktools_fal_images'],
            'fal images doch vorhanden'
        );
        $this->assertEmpty(
            $GLOBALS['TCA']['tt_news']['columns']['tx_mktools_fal_media'],
            'fal media doch vorhanden'
        );
    }
}
