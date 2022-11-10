<?php

declare(strict_types=1);

namespace DMK\Mktools\Seo\XmlSitemap;

use DMK\Mktools\Utility\SeoRobotsMetaTagUtility;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/***************************************************************
 * Copyright notice
 *
 * (c) DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class PagesDataProviderTest.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class PagesDataProviderTest extends UnitTestCase
{
    /**
     * @dataProvider dataProviderRemovePagesWithNoIndexRobotsMetaTag
     */
    public function testRemovePagesWithNoIndexRobotsMetaTag(
        int $pageUid,
        $robotsMetaTag,
        int $expectedCount
    ) {
        $pages = [
            0 => ['uid' => $pageUid],
        ];
        $provider = $this->getAccessibleMock(PagesDataProvider::class, ['dummy'], [], '', false);
        $robotsMetaTagUtility = $this->getMockBuilder(SeoRobotsMetaTagUtility::class)
            ->setMethods(['getSeoRobotsMetaTagValue'])
            ->getMock();
        GeneralUtility::addInstance(SeoRobotsMetaTagUtility::class, $robotsMetaTagUtility);
        $provider->_set('config', ['defaultRobotsMetaTag' => 'INDEX,FOLLOW']);
        $robotsMetaTagUtility
            ->expects(self::once())
            ->method('getSeoRobotsMetaTagValue')
            ->with('', ['default' => 'INDEX,FOLLOW'], $pageUid)
            ->willReturn($robotsMetaTag);

        self::assertCount($expectedCount, $provider->_call('removePagesWithNoIndexRobotsMetaTag', $pages));
    }

    public function dataProviderRemovePagesWithNoIndexRobotsMetaTag(): array
    {
        return [
            [123, 'INDEX,FOLLOW', 1],
            [123, 'INDEX,NOFOLLOW', 1],
            [123, 'NOINDEX,FOLLOW', 0],
            [123, 'NOINDEX,NOFOLLOW', 0],
            [123, 'NOODP,NOINDEX,FOLLOW', 0],
            [123, 'UNKNOWN', 1],
            [123, '', 1],
            [123, null, 1],
            [123, false, 1],
            [123, 0, 1],
        ];
    }
}
