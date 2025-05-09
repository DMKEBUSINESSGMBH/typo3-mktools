<?php

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

use DMK\Mktools\Utility\SeoRobotsMetaTagUtility;
use Sys25\RnBase\Testing\BaseTestCase;
use Sys25\RnBase\Utility\Misc;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * SeoRobotsMetaTagUtilityTest.
 *
 * @author          Hannes Bochmann
 * @author          Michael Wagner
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class SeoRobotsMetaTagUtilityTest extends BaseTestCase
{
    public function testGetSeoRobotsMetaTagValueReturnsDefaultValueWhenNoValueSetAndNoInheritedValueExists(): void
    {
        $util = $this->getMock(SeoRobotsMetaTagUtility::class, ['getRobotsValue']);
        $util->expects(self::once())
            ->method('getRobotsValue')
            ->willReturn(0);

        $value = $util->getSeoRobotsMetaTagValue('', ['default' => 'test']);

        self::assertEquals('test', $value, 'Falscher Wert zurückgeliefert');
    }

    public function testGetSeoRobotsMetaTagValueReturnsOptionByValueIfPositiveRobotsValueFound(): void
    {
        $util = $this->getMock(SeoRobotsMetaTagUtility::class, ['getRobotsValue']);
        $util->expects(self::once())
            ->method('getRobotsValue')
            ->willReturn(123);

        $util::$options[123] = 'robots tag value';

        $value = $util->getSeoRobotsMetaTagValue('', ['default' => 'test']);

        self::assertEquals('robots tag value', $value, 'Falscher Wert zurückgeliefert');
    }

    public function testGetSeoRobotsMetaTagValueReturnsOptionByValueIfNegativeRobotsValueFound(): void
    {
        $util = $this->getMock(SeoRobotsMetaTagUtility::class, ['getRobotsValue']);
        $util->expects(self::once())
            ->method('getRobotsValue')
            ->willReturn(-1);

        $value = $util->getSeoRobotsMetaTagValue('', ['default' => 'test']);

        self::assertEquals('test', $value, 'Falscher Wert zurückgeliefert');
    }

    public function testGetRootlineReturnsCorrectData(): void
    {
        self::markTestSkipped('Test need refactoring');

        Misc::prepareTSFE();
        $GLOBALS['TSFE']->id = 1;
        $rootline = $this->callInaccessibleMethod(
            GeneralUtility::makeInstance(SeoRobotsMetaTagUtility::class),
            'getRootline'
        );

        self::assertTrue(is_array($rootline), 'es wurde kein array geliefert');
        self::assertGreaterThan(0, count($rootline), 'es wurde ein leeres array geliefert');
        // haben wir scheinbar einen Seitendatensatz?
        self::assertArrayHasKey(
            'mkrobotsmetatag',
            $rootline[0],
            'der erste page Eintrag hat nicht das Feld mkrobotsmetatag. Evtl. den System Cache leeren?'
        );
    }

    public function testGetRobotsValueIfNoPagesInRootline(): void
    {
        $util = $this->getMock(SeoRobotsMetaTagUtility::class, ['getRootline']);
        $util->expects(self::once())
            ->method('getRootline')
            ->willReturn([]);

        self::assertSame(0, $this->callInaccessibleMethod($util, 'getRobotsValue'));
    }

    public function testGetRobotsValueIfNoPageInRootlineHasRobotsMetaTag(): void
    {
        $util = $this->getMock(SeoRobotsMetaTagUtility::class, ['getRootline']);
        $util->expects(self::once())
            ->method('getRootline')
            ->willReturn([0 => ['uid' => 123]]);

        self::assertSame(0, $this->callInaccessibleMethod($util, 'getRobotsValue'));
    }

    public function testGetRobotsValueIfPageInRootlineHasRobotsMetaTag(): void
    {
        $util = $this->getMock(SeoRobotsMetaTagUtility::class, ['getRootline']);
        $util->expects(self::once())
            ->method('getRootline')
            ->willReturn([0 => ['uid' => 123], 1 => ['mkrobotsmetatag' => 'NOINDEX']]);

        self::assertSame('NOINDEX', $this->callInaccessibleMethod($util, 'getRobotsValue'));
    }
}
