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

/**
 * @author Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mktools_tests_util_RealUrlTest extends tx_rnbase_tests_BaseTestCase
{
    /**
     * @var string
     */
    private $realUrlConfigurationFile;

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        if (!tx_rnbase_util_Extensions::isLoaded('realurl')) {
            $this->markTestSkipped('realurl ist nicht installiert');
        }

        \DMK\Mklib\Utility\Tests::storeExtConf('mktools');

        $this->realUrlConfigurationFile = tx_rnbase_util_Extensions::extPath('mktools').'tests/fixtures/realUrlConfig.php';
        \DMK\Mklib\Utility\Tests::setExtConfVar(
            'realUrlConfigurationFile',
            $this->realUrlConfigurationFile,
            'mktools'
        );
        \DMK\Mklib\Utility\Tests::setExtConfVar(
            'realUrlConfigurationTemplate',
            tx_rnbase_util_Extensions::extPath('mktools').'tests/fixtures/realUrlConfigTemplate.php',
            'mktools'
        );

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'] = [];
    }

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        \DMK\Mklib\Utility\Tests::restoreExtConf('mktools');
        @unlink($this->realUrlConfigurationFile);
    }

    /**
     * @return ux_tx_realurl
     */
    protected function getRealUrlInstance()
    {
        if (!tx_rnbase_util_Extensions::isLoaded('realurl')) {
            $this->markTestSkipped(
                'There is another allready registred xclass!'
            );
        }

        return tx_rnbase::makeInstance('tx_realurl');
    }

    /**
     * @group unit
     * @TODO refactoring seit realurl 2.x funktioniert diese xclass nicht mehr
     */
    public function testRegisterXclass()
    {
        if (!tx_rnbase_util_Extensions::isLoaded('realurl')) {
            $this->markTestSkipped(
                'There is another allready registred xclass!'
            );
        }

        if (!tx_mklib_util_MiscTools::getExtensionValue('realUrlXclass', 'mktools')) {
            self::markTestSkipped('die realurl xclass soll nicht eingebunden werden');
        }

        try {
            tx_mktools_util_RealUrl::registerXclass();
        } catch (LogicException $e) {
            if ($e->getCode() !== intval(ERROR_CODE_MKTOOLS.'130')) {
                throw $e;
            }
            $this->markTestSkipped(
                'There is another allready registred xclass!'
            );
        }
        $xclass = $this->getRealUrlInstance();

        $this->assertInstanceOf('ux_tx_realurl', $xclass);
    }

    /**
     * @group unit
     * @TODO refactoring seit realurl 2.x funktioniert diese xclass nicht mehr
     */
    public function testXclassGetLocalizedPostVarSet()
    {
        if (!tx_mklib_util_MiscTools::getExtensionValue('realUrlXclass', 'mktools')) {
            self::markTestSkipped('die realurl xclass soll nicht eingebunden werden');
        }

        $realUrl = $this->getRealUrlInstance();
        $realUrl->orig_paramKeyValues = [
            'id' => 50,
            'L' => 0, // default language (0=en,1=de,2=nl)
            'mktools[cat]' => 10, // test parameter 2
            'mktools[item]' => 10, // test parameter 2
        ];
        $rawSets = [
            'category' => [
                [
                    'GETvar' => 'mktools[cat]',
                    'language' => ['ids' => '0'], // default language (en)
                    'noMatch' => 'NULL',
                ],
            ],
            'kategorie' => [
                [
                    'GETvar' => 'mktools[cat]',
                    'language' => ['ids' => '1'], // de language
                    'noMatch' => 'NULL',
                ],
            ],
            'categorie' => [
                [
                    'GETvar' => 'mktools[cat]',
                    'language' => ['ids' => '2'], // nl language
                    'noMatch' => 'NULL',
                ],
            ],
            'item' => [
                [
                    'GETvar' => 'mktools[item]',
                    'language' => '0,2', // en & nl language
                    'noMatch' => 'NULL',
                ],
            ],
            'element' => [
                [
                    'GETvar' => 'mktools[item]',
                    'language' => '1', // de language
                    'noMatch' => 'NULL',
                ],
            ],
        ];

        // check for EN
        $realUrl->orig_paramKeyValues['L'] = 0;
        $this->callInaccessibleMethod($realUrl, 'setMode', $realUrl::MODE_ENCODE);
        $cleanedSets = $this->callInaccessibleMethod(
            $realUrl,
            'getLocalizedPostVarSet',
            $rawSets
        );
        $this->assertEquals(['category', 'item'], array_keys($cleanedSets));

        // check for DE
        $realUrl->orig_paramKeyValues['L'] = 1;
        $this->callInaccessibleMethod($realUrl, 'setMode', $realUrl::MODE_ENCODE);
        $cleanedSets = $this->callInaccessibleMethod(
            $realUrl,
            'getLocalizedPostVarSet',
            $rawSets
        );
        $this->assertEquals(['kategorie', 'element'], array_keys($cleanedSets));

        // check for NL
        $realUrl->orig_paramKeyValues['L'] = 2;
        $this->callInaccessibleMethod($realUrl, 'setMode', $realUrl::MODE_ENCODE);
        $cleanedSets = $this->callInaccessibleMethod(
            $realUrl,
            'getLocalizedPostVarSet',
            $rawSets
        );
        $this->assertEquals(['categorie', 'item'], array_keys($cleanedSets));

        // check for DECODE
        $realUrl->orig_paramKeyValues = []; // remove all vars, we decode
        $this->callInaccessibleMethod($realUrl, 'setMode', $realUrl::MODE_DECODE);
        $cleanedSets = $this->callInaccessibleMethod(
            $realUrl,
            'getLocalizedPostVarSet',
            $rawSets
        );
        // should be the same!
        $this->assertEquals(array_keys($rawSets), array_keys($cleanedSets));
        $this->assertEquals(($rawSets), ($cleanedSets));
    }

    /**
     * @group unit
     */
    public function testGetPagesWithFixedPostVarTypeCallsDoSelectCorrect()
    {
        $dbUtil = $this->getDbUtilMock();

        $expectedWhat = '*';
        $expectedFrom = 'pages';
        $expectedOptions = [
            'enablefieldsfe' => 1,
            'wrapperclass' => 'tx_mktools_model_Pages',
            'where' => 'tx_mktools_fixedpostvartype > 0',
        ];

        $dbUtil->expects($this->once())
            ->method('doSelect')
            ->with($expectedWhat, $expectedFrom, $expectedOptions)
            ->will($this->returnValue('test'));

        $realUrlUtil = $this->getMock(
            'tx_mktools_util_RealUrl',
            ['getDbUtil']
        );

        $realUrlUtil->expects($this->once())
            ->method('getDbUtil')
            ->will($this->returnValue($dbUtil));

        $this->assertEquals(
            'test',
            $realUrlUtil->getPagesWithFixedPostVarType(),
            'falscher rücgabewert'
        );
    }

    /**
     * @group unit
     */
    public function testAreTherePagesWithFixedPostVarTypeModifiedLaterThanCallsDoSelectCorrectAndReturnsTrueIfCount()
    {
        $modificationTimeStamp = 123;

        $dbUtil = $this->getDbUtilMock();

        $expectedWhat = 'COUNT(uid) AS uid_count';
        $expectedFrom = 'pages';
        $expectedOptions = [
            'enablefieldsfe' => 1,
            'where' => 'tx_mktools_fixedpostvartype > 0 AND tstamp > '.
                                    $modificationTimeStamp,
        ];

        $dbUtil->expects($this->once())
            ->method('doSelect')
            ->with($expectedWhat, $expectedFrom, $expectedOptions)
            ->will($this->returnValue([0 => ['uid_count' => 456]]));

        $realUrlUtil = $this->getMock(
            'tx_mktools_util_RealUrl',
            ['getDbUtil']
        );

        $realUrlUtil->expects($this->once())
            ->method('getDbUtil')
            ->will($this->returnValue($dbUtil));

        $this->assertTrue(
            $realUrlUtil->areTherePagesWithFixedPostVarTypeModifiedLaterThan(
                $modificationTimeStamp
            )
        );
    }

    /**
     * @group unit
     */
    public function testAreTherePagesWithFixedPostVarTypeModifiedLaterThanCallsDoSelectCorrectAndReturnsFalseIfNoCount()
    {
        $modificationTimeStamp = 123;

        $dbUtil = $this->getDbUtilMock();

        $expectedWhat = 'COUNT(uid) AS uid_count';
        $expectedFrom = 'pages';
        $expectedOptions = [
            'enablefieldsfe' => 1,
            'where' => 'tx_mktools_fixedpostvartype > 0 AND tstamp > '.
                                    $modificationTimeStamp,
        ];

        $dbUtil->expects($this->once())
            ->method('doSelect')
            ->with($expectedWhat, $expectedFrom, $expectedOptions)
            ->will($this->returnValue([0 => ['uid_count' => 0]]));

        $realUrlUtil = $this->getMock(
            'tx_mktools_util_RealUrl',
            ['getDbUtil']
        );

        $realUrlUtil->expects($this->once())
            ->method('getDbUtil')
            ->will($this->returnValue($dbUtil));

        $this->assertFalse(
            $realUrlUtil->areTherePagesWithFixedPostVarTypeModifiedLaterThan(
                $modificationTimeStamp
            )
        );
    }

    /**
     * @return Tx_Rnbase_Database_Connection
     */
    private function getDbUtilMock()
    {
        return $this->getMock(
            'Tx_Rnbase_Database_Connection',
            ['doSelect']
        );
    }

    /**
     * @group unit
     */
    public function testNeedsRealUrlConfigurationToBeGeneratedCallsAreTherePagesWithFixedPostVarTypeModifiedLaterThanWithTimestampZero()
    {
        \DMK\Mklib\Utility\Tests::setExtConfVar(
            'realUrlConfigurationFile',
            'unknown',
            'mktools'
        );

        $realUrlUtil = $this->getMock(
            'tx_mktools_util_RealUrl',
            [
                'areTherePagesWithFixedPostVarTypeModifiedLaterThan',
                'areThereFixedPostVarTypesModifiedLaterThan',
            ]
        );

        $expectedTimeStamp = 0;
        $realUrlUtil->expects($this->once())
            ->method('areTherePagesWithFixedPostVarTypeModifiedLaterThan')
            ->with($expectedTimeStamp);

        $realUrlUtil->needsRealUrlConfigurationToBeGenerated();
    }

    /**
     * @group unit
     */
    public function testNeedsRealUrlConfigurationToBeGeneratedCallsAreTherePagesWithFixedPostVarTypeModifiedLaterThanWithTimestampOfConfigFile()
    {
        $realUrlUtil = $this->getMock(
            'tx_mktools_util_RealUrl',
            [
                'areTherePagesWithFixedPostVarTypeModifiedLaterThan',
                'areThereFixedPostVarTypesModifiedLaterThan',
            ]
        );

        touch($this->realUrlConfigurationFile);
        $expectedTimeStamp = filemtime($this->realUrlConfigurationFile);
        $realUrlUtil->expects($this->once())
            ->method('areTherePagesWithFixedPostVarTypeModifiedLaterThan')
            ->with($expectedTimeStamp);

        $realUrlUtil->needsRealUrlConfigurationToBeGenerated();
    }

    /**
     * @group unit
     */
    public function testNeedsRealUrlConfigurationToBeGeneratedCallsAreThereFixedPostVarTypesModifiedLaterThanWithTimestampZero()
    {
        \DMK\Mklib\Utility\Tests::setExtConfVar(
            'realUrlConfigurationFile',
            'unknown',
            'mktools'
        );

        $realUrlUtil = $this->getMock(
            'tx_mktools_util_RealUrl',
            [
                'areTherePagesWithFixedPostVarTypeModifiedLaterThan',
                'areThereFixedPostVarTypesModifiedLaterThan',
            ]
        );

        $expectedTimeStamp = 0;
        $realUrlUtil->expects($this->once())
            ->method('areThereFixedPostVarTypesModifiedLaterThan')
            ->with($expectedTimeStamp);

        $realUrlUtil->needsRealUrlConfigurationToBeGenerated();
    }

    /**
     * @group unit
     */
    public function testNeedsRealUrlConfigurationToBeGeneratedCallsAreThereFixedPostVarTypesModifiedLaterThanWithTimestampOfConfigFile()
    {
        $realUrlUtil = $this->getMock(
            'tx_mktools_util_RealUrl',
            [
                'areTherePagesWithFixedPostVarTypeModifiedLaterThan',
                'areThereFixedPostVarTypesModifiedLaterThan',
            ]
        );

        touch($this->realUrlConfigurationFile);
        $expectedTimeStamp = filemtime($this->realUrlConfigurationFile);
        $realUrlUtil->expects($this->once())
            ->method('areThereFixedPostVarTypesModifiedLaterThan')
            ->with($expectedTimeStamp);

        $realUrlUtil->needsRealUrlConfigurationToBeGenerated();
    }

    /**
     * @group unit
     */
    public function testNeedsRealUrlConfigurationToBeGeneratedCallsReturnsTrueIfPagesAndFixedPostVarTypesWereModified()
    {
        \DMK\Mklib\Utility\Tests::setExtConfVar(
            'realUrlConfigurationFile',
            'unknown',
            'mktools'
        );

        $realUrlUtil = $this->getMock(
            'tx_mktools_util_RealUrl',
            [
                'areTherePagesWithFixedPostVarTypeModifiedLaterThan',
                'areThereFixedPostVarTypesModifiedLaterThan',
            ]
        );

        $realUrlUtil->expects($this->once())
            ->method('areTherePagesWithFixedPostVarTypeModifiedLaterThan')
            ->will($this->returnValue(true));
        $realUrlUtil->expects($this->once())
            ->method('areThereFixedPostVarTypesModifiedLaterThan')
            ->will($this->returnValue(true));

        $this->assertTrue(
            $realUrlUtil->needsRealUrlConfigurationToBeGenerated()
        );
    }

    /**
     * @group unit
     */
    public function testNeedsRealUrlConfigurationToBeGeneratedCallsReturnsTrueIfOnlyPagesWereModified()
    {
        \DMK\Mklib\Utility\Tests::setExtConfVar(
            'realUrlConfigurationFile',
            'unknown',
            'mktools'
        );

        $realUrlUtil = $this->getMock(
            'tx_mktools_util_RealUrl',
            [
                'areTherePagesWithFixedPostVarTypeModifiedLaterThan',
                'areThereFixedPostVarTypesModifiedLaterThan',
            ]
        );

        $realUrlUtil->expects($this->once())
            ->method('areTherePagesWithFixedPostVarTypeModifiedLaterThan')
            ->will($this->returnValue(true));
        $realUrlUtil->expects($this->once())
            ->method('areThereFixedPostVarTypesModifiedLaterThan')
            ->will($this->returnValue(false));

        $this->assertTrue(
            $realUrlUtil->needsRealUrlConfigurationToBeGenerated()
        );
    }

    /**
     * @group unit
     */
    public function testNeedsRealUrlConfigurationToBeGeneratedCallsReturnsTrueIfOnlyFixedPostVarTypesWereModified()
    {
        \DMK\Mklib\Utility\Tests::setExtConfVar(
            'realUrlConfigurationFile',
            'unknown',
            'mktools'
        );

        $realUrlUtil = $this->getMock(
            'tx_mktools_util_RealUrl',
            [
                'areTherePagesWithFixedPostVarTypeModifiedLaterThan',
                'areThereFixedPostVarTypesModifiedLaterThan',
                'isTemplateFileModifiedLaterThan',
            ]
        );

        $realUrlUtil->expects($this->once())
            ->method('areTherePagesWithFixedPostVarTypeModifiedLaterThan')
            ->will($this->returnValue(false));
        $realUrlUtil->expects($this->once())
            ->method('areThereFixedPostVarTypesModifiedLaterThan')
            ->will($this->returnValue(true));
        $realUrlUtil->expects($this->once())
            ->method('isTemplateFileModifiedLaterThan')
            ->will($this->returnValue(false));

        $this->assertTrue(
            $realUrlUtil->needsRealUrlConfigurationToBeGenerated()
        );
    }

    /**
     * @group unit
     */
    public function testNeedsRealUrlConfigurationToBeGeneratedCallsReturnsFalseIfPagesAndFixedPostVarTypesAndTemplateFileWerenotModified()
    {
        \DMK\Mklib\Utility\Tests::setExtConfVar(
            'realUrlConfigurationFile',
            'unknown',
            'mktools'
        );

        $realUrlUtil = $this->getMock(
            'tx_mktools_util_RealUrl',
            [
                'areTherePagesWithFixedPostVarTypeModifiedLaterThan',
                'areThereFixedPostVarTypesModifiedLaterThan',
                'isTemplateFileModifiedLaterThan',
            ]
        );

        $realUrlUtil->expects($this->once())
            ->method('areTherePagesWithFixedPostVarTypeModifiedLaterThan')
            ->will($this->returnValue(false));
        $realUrlUtil->expects($this->once())
            ->method('areThereFixedPostVarTypesModifiedLaterThan')
            ->will($this->returnValue(false));
        $realUrlUtil->expects($this->once())
            ->method('isTemplateFileModifiedLaterThan')
            ->will($this->returnValue(false));

        $this->assertFalse(
            $realUrlUtil->needsRealUrlConfigurationToBeGenerated()
        );
    }

    /**
     * @group unit
     */
    public function testNeedsRealUrlConfigurationToBeGeneratedCallsReturnsTrueIfOnlyTemplateFileWasModified()
    {
        \DMK\Mklib\Utility\Tests::setExtConfVar(
            'realUrlConfigurationFile',
            'unknown',
            'mktools'
        );

        $realUrlUtil = $this->getMock(
            'tx_mktools_util_RealUrl',
            [
                'areTherePagesWithFixedPostVarTypeModifiedLaterThan',
                'areThereFixedPostVarTypesModifiedLaterThan',
                'isTemplateFileModifiedLaterThan',
            ]
        );

        $realUrlUtil->expects($this->once())
            ->method('areTherePagesWithFixedPostVarTypeModifiedLaterThan')
            ->will($this->returnValue(false));
        $realUrlUtil->expects($this->once())
            ->method('areThereFixedPostVarTypesModifiedLaterThan')
            ->will($this->returnValue(false));
        $realUrlUtil->expects($this->once())
            ->method('isTemplateFileModifiedLaterThan')
            ->will($this->returnValue(true));

        $this->assertTrue(
            $realUrlUtil->needsRealUrlConfigurationToBeGenerated()
        );
    }

    /**
     * @group unit
     */
    public function testGenerateSerializedRealUrlConfigurationFileByPagesGeneratesFileEvenIfNoPagesGiven()
    {
        $this->assertTrue(
            tx_rnbase::makeInstance('tx_mktools_util_RealUrl')->generateSerializedRealUrlConfigurationFileByPages([])
        );

        $this->assertEquals(
            file_get_contents(tx_rnbase_util_Extensions::extPath('mktools').'tests/fixtures/expectedRealUrlConfig3.php'),
            file_get_contents($this->realUrlConfigurationFile),
            'Datei falsch generiert'
        );
    }

    /**
     * @group unit
     */
    public function testGenerateSerializedRealUrlConfigurationFileByPagesGeneratesNoFileIfPagesGivenButNoTemplate()
    {
        \DMK\Mklib\Utility\Tests::setExtConfVar(
            'realUrlConfigurationTemplate',
            tx_rnbase_util_Extensions::extPath('mktools').'tests/fixtures/empty',
            'mktools'
        );
        $pages = [
            0 => tx_rnbase::makeInstance(
                'tx_mktools_model_Pages',
                [
                    'tx_mktools_fixedpostvartype' => ['identifier' => 'firstIdentifier'],
                    'uid' => 1,
                ]
            ),
        ];
        $this->assertFalse(
            tx_rnbase::makeInstance('tx_mktools_util_RealUrl')->generateSerializedRealUrlConfigurationFileByPages($pages)
        );

        $this->assertFileNotExists($this->realUrlConfigurationFile, 'Datei doch generiert.');
    }

    /**
     * @group unit
     */
    public function testGenerateSerializedRealUrlConfigurationFileByPagesGeneratesNoFileIfPagesGivenButNoDestinationFileConfigured()
    {
        \DMK\Mklib\Utility\Tests::setExtConfVar(
            'realUrlConfigurationFile',
            '',
            'mktools'
        );
        $pages = [
            0 => tx_rnbase::makeInstance(
                'tx_mktools_model_Pages',
                [
                    'tx_mktools_fixedpostvartype' => ['identifier' => 'firstIdentifier'],
                    'uid' => 1,
                ]
            ),
        ];
        $this->assertFalse(
            tx_rnbase::makeInstance('tx_mktools_util_RealUrl')->generateSerializedRealUrlConfigurationFileByPages($pages)
        );

        $this->assertFileNotExists($this->realUrlConfigurationFile, 'Datei doch generiert.');
    }

    /**
     * @group unit
     */
    public function testGenerateSerializedRealUrlConfigurationFileByPagesGeneratesFileCorrectIfPagesGiven()
    {
        $pages = [
            0 => tx_rnbase::makeInstance(
                'tx_mktools_model_Pages',
                [
                    'tx_mktools_fixedpostvartype' => ['identifier' => 'firstIdentifier'],
                    'uid' => 1,
                ]
            ),
            1 => tx_rnbase::makeInstance(
                'tx_mktools_model_Pages',
                [
                    'tx_mktools_fixedpostvartype' => ['identifier' => 'secondIdentifier'],
                    'uid' => 2,
                ]
            ),
        ];
        $this->assertTrue(
            tx_rnbase::makeInstance('tx_mktools_util_RealUrl')->generateSerializedRealUrlConfigurationFileByPages(
                $pages,
                false
            ),
            'datei doch nicht geschrieben'
        );

        $this->assertEquals(
            file_get_contents(tx_rnbase_util_Extensions::extPath('mktools').'tests/fixtures/expectedRealUrlConfig.php'),
            file_get_contents($this->realUrlConfigurationFile),
            'Datei falsch generiert'
        );
    }

    /**
     * @group unit
     */
    public function testGenerateSerializedRealUrlConfigurationFileByPagesGeneratesFileCorrectIfMarkerExistsSeveralTimes()
    {
        \DMK\Mklib\Utility\Tests::setExtConfVar(
            'realUrlConfigurationTemplate',
            tx_rnbase_util_Extensions::extPath('mktools').'tests/fixtures/realUrlConfigTemplate2.php',
            'mktools'
        );

        $pages = [
            0 => tx_rnbase::makeInstance(
                'tx_mktools_model_Pages',
                [
                    'tx_mktools_fixedpostvartype' => ['identifier' => 'firstIdentifier'],
                    'uid' => 1,
                ]
            ),
        ];
        $this->assertTrue(
            tx_rnbase::makeInstance('tx_mktools_util_RealUrl')->generateSerializedRealUrlConfigurationFileByPages(
                $pages,
                false
            ),
            'datei doch nicht geschrieben'
        );

        $this->assertEquals(
            file_get_contents(tx_rnbase_util_Extensions::extPath('mktools').'tests/fixtures/expectedRealUrlConfig2.php'),
            file_get_contents($this->realUrlConfigurationFile),
            'Datei falsch generiert'
        );
    }

    /**
     * @group unit
     */
    public function testAreThereFixedPostVarTypesModifiedLaterThanCallsDoSelectCorrectAndReturnsTrueIfCount()
    {
        $modificationTimeStamp = 123;

        $dbUtil = $this->getDbUtilMock();

        $expectedWhat = 'COUNT(uid) AS uid_count';
        $expectedFrom = 'tx_mktools_fixedpostvartypes';
        $expectedOptions = [
            'enablefieldsfe' => 1,
            'where' => 'tstamp > '.$modificationTimeStamp,
        ];

        $dbUtil->expects($this->once())
            ->method('doSelect')
            ->with($expectedWhat, $expectedFrom, $expectedOptions)
            ->will($this->returnValue([0 => ['uid_count' => 456]]));

        $realUrlUtil = $this->getMock(
            'tx_mktools_util_RealUrl',
            ['getDbUtil']
        );

        $realUrlUtil->expects($this->once())
            ->method('getDbUtil')
            ->will($this->returnValue($dbUtil));

        $this->assertTrue(
            $realUrlUtil->areThereFixedPostVarTypesModifiedLaterThan(
                $modificationTimeStamp
            )
        );
    }

    /**
     * @group unit
     */
    public function testAreThereFixedPostVarTypesModifiedLaterThanCallsDoSelectCorrectAndReturnsFalseIfNoCount()
    {
        $modificationTimeStamp = 123;

        $dbUtil = $this->getDbUtilMock();

        $expectedWhat = 'COUNT(uid) AS uid_count';
        $expectedFrom = 'tx_mktools_fixedpostvartypes';
        $expectedOptions = [
            'enablefieldsfe' => 1,
            'where' => 'tstamp > '.$modificationTimeStamp,
        ];

        $dbUtil->expects($this->once())
            ->method('doSelect')
            ->with($expectedWhat, $expectedFrom, $expectedOptions)
            ->will($this->returnValue([0 => ['uid_count' => 0]]));

        $realUrlUtil = $this->getMock(
            'tx_mktools_util_RealUrl',
            ['getDbUtil']
        );

        $realUrlUtil->expects($this->once())
            ->method('getDbUtil')
            ->will($this->returnValue($dbUtil));

        $this->assertFalse(
            $realUrlUtil->areThereFixedPostVarTypesModifiedLaterThan(
                $modificationTimeStamp
            )
        );
    }

    /**
     * @group unit
     */
    public function testGenerateSerializedRealUrlConfigurationFileByPagesGeneratesFileCorrectIfTypoConfVarsVariableIsUsed()
    {
        \DMK\Mklib\Utility\Tests::setExtConfVar(
            'realUrlConfigurationTemplate',
            tx_rnbase_util_Extensions::extPath('mktools').'tests/fixtures/realUrlConfigTemplate3.php',
            'mktools'
        );

        $pages = [
            0 => tx_rnbase::makeInstance(
                'tx_mktools_model_Pages',
                [
                    'tx_mktools_fixedpostvartype' => ['identifier' => 'firstIdentifier'],
                    'uid' => 1,
                ]
            ),
        ];
        $this->assertTrue(
            tx_rnbase::makeInstance('tx_mktools_util_RealUrl')->generateSerializedRealUrlConfigurationFileByPages(
                $pages,
                false
            ),
            'datei doch nicht geschrieben'
        );

        $this->assertEquals(
            file_get_contents(tx_rnbase_util_Extensions::extPath('mktools').'tests/fixtures/expectedRealUrlConfig2.php'),
            file_get_contents($this->realUrlConfigurationFile),
            'Datei falsch generiert'
        );
    }
}
