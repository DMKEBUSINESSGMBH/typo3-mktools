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
tx_rnbase::load('tx_mklib_tests_Util');
tx_rnbase::load('tx_rnbase_util_Misc');
tx_rnbase::load('tx_mktools_util_SeoRobotsMetaTag');
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('tx_mktools_util_miscTools');

/**
 * tx_mktools_tests_util_SeoRobotsMetaTag_testcase
 *
 * @package         TYPO3
 * @subpackage      mktools
 * @author          Hannes Bochmann
 * @author          Michael Wagner
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_mktools_tests_util_SeoRobotsMetaTag_testcase extends tx_rnbase_tests_BaseTestCase
{

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        if (!tx_mktools_util_miscTools::isSeoRobotsMetaTagActive()) {
            $this->markTestSkipped('SEO Robots Metatag Feature not activated in extension manager');
        }
    }

    /**
     * @group unit
     */
    public function testGetSeoRobotsMetaTagValueReturnsDefaultValueWhenNoValueSetAndNoInheritedValueExists()
    {
        $util = $this->getMock('tx_mktools_util_SeoRobotsMetaTag', array('getRobotsValue'));
        $util->expects(self::once())
            ->method('getRobotsValue')
            ->will(self::returnValue(0));

        $value = $util->getSeoRobotsMetaTagValue('', array('default' => 'test'));

        self::assertEquals('test', $value, 'Falscher Wert zurückgeliefert');
    }

    /**
     * @group unit
     */
    public function testGetSeoRobotsMetaTagValueReturnsOptionByValueIfPositiveRobotsValueFound()
    {
        $util = $this->getMock('tx_mktools_util_SeoRobotsMetaTag', array('getRobotsValue'));
        $util->expects(self::once())
            ->method('getRobotsValue')
            ->will(self::returnValue(123));

        $util::$options[123] = 'robots tag value';

        $value = $util->getSeoRobotsMetaTagValue('', array('default' => 'test'));

        self::assertEquals('robots tag value', $value, 'Falscher Wert zurückgeliefert');
    }

    /**
     * @group unit
     */
    public function testGetSeoRobotsMetaTagValueReturnsOptionByValueIfNegativeRobotsValueFound()
    {
        $util = $this->getMock('tx_mktools_util_SeoRobotsMetaTag', array('getRobotsValue'));
        $util->expects(self::once())
            ->method('getRobotsValue')
            ->will(self::returnValue(-1));

        $value = $util->getSeoRobotsMetaTagValue('', array('default' => 'test'));

        self::assertEquals('test', $value, 'Falscher Wert zurückgeliefert');
    }

    /**
     * @group unit
     */
    public function testGetRootlineReturnsCorrectData()
    {
        tx_rnbase_util_Misc::prepareTSFE();
        $GLOBALS['TSFE']->id = 1;
        $rootline = $this->callInaccessibleMethod(
            tx_rnbase::makeInstance('tx_mktools_util_SeoRobotsMetaTag'),
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
        $util = $this->getMock('tx_mktools_util_SeoRobotsMetaTag', array('getRootline'));
        $util->expects(self::once())
            ->method('getRootline')
            ->will(self::returnValue(array()));

        self::assertSame(0, $this->callInaccessibleMethod($util, 'getRobotsValue'));
    }

    /**
     * @group unit
     */
    public function testGetRobotsValueIfNoPageInRootlineHasRobotsMetaTag()
    {
        $util = $this->getMock('tx_mktools_util_SeoRobotsMetaTag', array('getRootline'));
        $util->expects(self::once())
            ->method('getRootline')
            ->will(self::returnValue(array(0 => array('uid' => 123))));

        self::assertSame(0, $this->callInaccessibleMethod($util, 'getRobotsValue'));
    }

    /**
     * @group unit
     */
    public function testGetRobotsValueIfPageInRootlineHasRobotsMetaTag()
    {
        $util = $this->getMock('tx_mktools_util_SeoRobotsMetaTag', array('getRootline'));
        $util->expects(self::once())
            ->method('getRootline')
            ->will(self::returnValue(array(0 => array('uid' => 123), 1 => array('mkrobotsmetatag' => 'NOINDEX'))));

        self::assertSame('NOINDEX', $this->callInaccessibleMethod($util, 'getRobotsValue'));
    }
}
