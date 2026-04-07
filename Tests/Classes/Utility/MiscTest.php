<?php

declare(strict_types=1);

/*
 * Copyright notice
 *
 * (c) DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This file is part of the "mktools" Extension for TYPO3 CMS.
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GNU Lesser General Public License can be found at
 * www.gnu.org/licenses/lgpl.html
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

namespace DMK\Mktools\Tests\Utility;

use DMK\Mktools\Tests\BaseTestCase;
use DMK\Mktools\Utility\Misc;

/**
 * Class MiscTest.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class MiscTest extends BaseTestCase
{
    /**
     * @var string
     */
    private mixed $defaultPageTsConfig;

    protected function setUp(): void
    {
        $this->defaultPageTsConfig = $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultPageTSconfig'];
        $this->storeExtConf('mktools');
    }

    protected function tearDown(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultPageTSconfig'] = $this->defaultPageTsConfig;
        $this->restoreExtConf('mktools');
    }

    public function testGetConfigurationsLoadsConfigCorrect(): void
    {
        self::markTestSkipped(
            'This test has to be refactored.'
        );

        $configurations = Misc::getConfigurations(
            'EXT:mktools/tests/fixtures/typoscript/miscTools1.txt'
        );

        $this->assertEquals(
            'config',
            $configurations->get('errorhandling.exceptionPage'),
            'Konfiguration nicht korrekt geladen'
        );
    }

    public function testGetConfigurationsPrefersPluginConfigurationOverConfigConfiguration(): void
    {
        self::markTestSkipped(
            'This test has to be refactored.'
        );

        $configurations = Misc::getConfigurations(
            'EXT:mktools/Configuration/TypoScript/errorhandling/setup.txt',
            'EXT:mktools/tests/fixtures/typoscript/miscTools2.txt'
        );

        $this->assertEquals(
            'plugin',
            $configurations->get('errorhandling.exceptionPage'),
            'plugin Konfiguration nicht bevorzugt'
        );
    }
}
