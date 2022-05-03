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
 * tx_mktools_tests_util_SeoRobotsMetaTagTest.
 *
 * @author          Hannes Bochmann
 * @author          Michael Wagner
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_mktools_tests_util_SeoRobotsMetaTagTest extends \Sys25\RnBase\Testing\BaseTestCase
{
    protected function setUp(): void
    {
        self::markTestSkipped('Problem with type3 9.5 config');

        if (!\DMK\Mktools\Utility\Misc::isSeoRobotsMetaTagActive()) {
            $this->markTestSkipped('SEO Robots Metatag Feature not activated in extension manager');
        }
    }

    /**
     * @group unit
     */
    public function testGetSeoRobotsMetaTagValueReturnsDefaultValueWhenNoValueSetAndNoInheritedValueExists()
    {
        self::markTestSkipped('Problem with type3 9.5 config');

        $util = $this->getMock(\DMK\Mktools\Utility\SeoRobotsMetaTagUtility::class, ['getRobotsValue']);
        $util->expects(self::once())
            ->method('getRobotsValue')
            ->will(self::returnValue(0));

        $value = $util->getSeoRobotsMetaTagValue('', ['default' => 'test']);

        self::assertEquals('test', $value, 'Falscher Wert zurückgeliefert');
    }

    /**
     * @group unit
     */
    public function testGetSeoRobotsMetaTagValueReturnsOptionByValueIfPositiveRobotsValueFound()
    {
        self::markTestSkipped('Problem with type3 9.5 config');
        /*
                $util = $this->getMock(DMK\Mktools\Utility\SeoRobotsMetaTagUtility::class, array('getRobotsValue'));
                $util->expects(self::once())
                    ->method('getRobotsValue')
                    ->will(self::returnValue(123));

                $util::$options[123] = 'robots tag value';

                $value = $util->getSeoRobotsMetaTagValue('', ['default' => 'test']);

                self::assertEquals('robots tag value', $value, 'Falscher Wert zurückgeliefert');
        */
    }

    /**
     * @group unit
     */
    public function testGetSeoRobotsMetaTagValueReturnsOptionByValueIfNegativeRobotsValueFound()
    {
        self::markTestSkipped('Problem with type3 9.5 config');

        $util = $this->getMock(DMK\Mktools\Utility\SeoRobotsMetaTagUtility::class, ['getRobotsValue']);
        $util->expects(self::once())
            ->method('getRobotsValue')
            ->will(self::returnValue(-1));

        $value = $util->getSeoRobotsMetaTagValue('', ['default' => 'test']);

        self::assertEquals('test', $value, 'Falscher Wert zurückgeliefert');
    }

    /**
     * @group unit
     */
    public function testGetRootlineReturnsCorrectData()
    {
        \Sys25\RnBase\Utility\Misc::prepareTSFE();
        $GLOBALS['TSFE']->id = 1;
        $rootline = $this->callInaccessibleMethod(
            \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(DMK\Mktools\Utility\SeoRobotsMetaTagUtility::class),
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

    /**
     * @group unit
     */
    public function testGetRobotsValueIfNoPagesInRootline()
    {
        $util = $this->getMock(DMK\Mktools\Utility\SeoRobotsMetaTagUtility::class, ['getRootline']);
        $util->expects(self::once())
            ->method('getRootline')
            ->will(self::returnValue([]));

        self::assertSame(0, $this->callInaccessibleMethod($util, 'getRobotsValue'));
    }

    /**
     * @group unit
     */
    public function testGetRobotsValueIfNoPageInRootlineHasRobotsMetaTag()
    {
        $util = $this->getMock(DMK\Mktools\Utility\SeoRobotsMetaTagUtility::class, ['getRootline']);
        $util->expects(self::once())
            ->method('getRootline')
            ->will(self::returnValue([0 => ['uid' => 123]]));

        self::assertSame(0, $this->callInaccessibleMethod($util, 'getRobotsValue'));
    }

    /**
     * @group unit
     */
    public function testGetRobotsValueIfPageInRootlineHasRobotsMetaTag()
    {
        $util = $this->getMock(DMK\Mktools\Utility\SeoRobotsMetaTagUtility::class, ['getRootline']);
        $util->expects(self::once())
            ->method('getRootline')
            ->will(self::returnValue([0 => ['uid' => 123], 1 => ['mkrobotsmetatag' => 'NOINDEX']]));

        self::assertSame('NOINDEX', $this->callInaccessibleMethod($util, 'getRobotsValue'));
    }
}
