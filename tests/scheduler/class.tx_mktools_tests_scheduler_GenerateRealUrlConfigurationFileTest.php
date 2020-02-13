<?php
/*
 *
 *  Copyright notice
 *
 *  (c) 2011 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

/**
 * @author Hannes Bochmann
 */
class tx_mktools_tests_scheduler_GenerateRealUrlConfigurationFileTest extends tx_rnbase_tests_BaseTestCase
{
    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        \DMK\Mklib\Utility\Tests::disableDevlog();
    }

    /**
     * @group unit
     */
    public function testExecuteTaskCallsNotGenerationOfConfigFileIfNotNecessary()
    {
        self::markTestIncomplete(
            'This test has to be refactored.'
        );

        $realUrlUtil = $this->getRealUrlUtilMock();

        $realUrlUtil->expects($this->once())
            ->method('needsRealUrlConfigurationToBeGenerated')
            ->will($this->returnValue(false));

        $realUrlUtil->expects($this->never())
            ->method('getPagesWithFixedPostVarType');

        $realUrlUtil->expects($this->never())
            ->method('generateSerializedRealUrlConfigurationFileByPages');

        $scheduler = $this->getMock(
            'tx_mktools_scheduler_GenerateRealUrlConfigurationFile',
            ['getRealUrlUtil']
        );

        $scheduler->expects($this->once())
            ->method('getRealUrlUtil')
            ->will($this->returnValue($realUrlUtil));

        $options = $devLog = [];
        $executeTaskMethod = new ReflectionMethod(
            'tx_mktools_scheduler_GenerateRealUrlConfigurationFile',
            'executeTask'
        );
        $executeTaskMethod->setAccessible(true);
        $arguments = [$options, &$devLog];
        $executeTaskMethod->invokeArgs($scheduler, $arguments);

        $expectedDevLog = [
            tx_rnbase_util_Logger::LOGLEVEL_INFO => [
                'message' => 'realUrl Konfigurationsdatei muss nicht erstellt werden.',
            ],
        ];

        $this->assertEquals($expectedDevLog, $devLog, 'devlog falsch');
    }

    /**
     * @group unit
     */
    public function testExecuteTaskCallsGenerationOfConfigFileIfNecessaryAndSetsCorrectDevLogIfGenerationWasSuccessful()
    {
        self::markTestIncomplete(
            'This test has to be refactored.'
        );

        $realUrlUtil = $this->getRealUrlUtilMock();

        $realUrlUtil->expects($this->once())
            ->method('needsRealUrlConfigurationToBeGenerated')
            ->will($this->returnValue(true));

        $realUrlUtil->expects($this->once())
            ->method('getPagesWithFixedPostVarType')
            ->will($this->returnValue(['mypages']));

        $realUrlUtil->expects($this->once())
            ->method('generateSerializedRealUrlConfigurationFileByPages')
            ->with(['mypages'])
            ->will($this->returnValue(true));

        $scheduler = $this->getMock(
            'tx_mktools_scheduler_GenerateRealUrlConfigurationFile',
            ['getRealUrlUtil']
        );

        $scheduler->expects($this->once())
            ->method('getRealUrlUtil')
            ->will($this->returnValue($realUrlUtil));

        $options = $devLog = [];
        $executeTaskMethod = new ReflectionMethod(
            'tx_mktools_scheduler_GenerateRealUrlConfigurationFile',
            'executeTask'
        );
        $executeTaskMethod->setAccessible(true);
        $arguments = [$options, &$devLog];
        $executeTaskMethod->invokeArgs($scheduler, $arguments);

        $expectedDevLog = [
            tx_rnbase_util_Logger::LOGLEVEL_INFO => [
                'message' => 'realUrl Konfigurationsdatei wurde neu erstellt.',
            ],
        ];

        $this->assertEquals($expectedDevLog, $devLog, 'devlog falsch');
    }

    /**
     * @group unit
     */
    public function testExecuteTaskCallsGenerationOfConfigFileIfNecessaryAndSetsCorrectDevLogIfGenerationWasNotSuccessful()
    {
        self::markTestIncomplete(
            'This test has to be refactored.'
        );

        $realUrlUtil = $this->getRealUrlUtilMock();

        $realUrlUtil->expects($this->once())
            ->method('needsRealUrlConfigurationToBeGenerated')
            ->will($this->returnValue(true));

        $realUrlUtil->expects($this->once())
            ->method('getPagesWithFixedPostVarType')
            ->will($this->returnValue(['mypages']));

        $realUrlUtil->expects($this->once())
            ->method('generateSerializedRealUrlConfigurationFileByPages')
            ->with(['mypages'])
            ->will($this->returnValue(false));

        $scheduler = $this->getMock(
            'tx_mktools_scheduler_GenerateRealUrlConfigurationFile',
            ['getRealUrlUtil']
        );

        $scheduler->expects($this->once())
            ->method('getRealUrlUtil')
            ->will($this->returnValue($realUrlUtil));

        $options = $devLog = [];
        $executeTaskMethod = new ReflectionMethod(
            'tx_mktools_scheduler_GenerateRealUrlConfigurationFile',
            'executeTask'
        );
        $executeTaskMethod->setAccessible(true);
        $arguments = [$options, &$devLog];
        $executeTaskMethod->invokeArgs($scheduler, $arguments);

        $expectedDevLog = [
            tx_rnbase_util_Logger::LOGLEVEL_INFO => [
                'message' => 'realUrl Konfigurationsdatei musste neu erstellt werden, was nicht funktioniert hat. Entweder stimmt die Extension Konfiguration nicht oder es gab einen Fehler beim Schreiben der Datei.',
            ],
        ];

        $this->assertEquals($expectedDevLog, $devLog, 'devlog falsch');
    }

    /**
     * @return tx_mktools_util_RealUrl
     */
    private function getRealUrlUtilMock()
    {
        return $this->getMock(
            'tx_mktools_util_RealUrl',
            [
                'needsRealUrlConfigurationToBeGenerated',
                'getPagesWithFixedPostVarType',
                'generateSerializedRealUrlConfigurationFileByPages',
            ]
        );
    }
}
