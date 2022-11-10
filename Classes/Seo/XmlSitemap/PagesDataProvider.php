<?php

declare(strict_types=1);

namespace DMK\Mktools\Seo\XmlSitemap;

use DMK\Mktools\Utility\SeoRobotsMetaTagUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Seo\XmlSitemap\PagesXmlSitemapDataProvider;

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
 * Class PagesDataProvider.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class PagesDataProvider extends PagesXmlSitemapDataProvider
{
    protected function getPages(): array
    {
        return $this->removePagesWithNoIndexRobotsMetaTag(parent::getPages());
    }

    protected function removePagesWithNoIndexRobotsMetaTag(array $pages): array
    {
        $robotsMetaTagUtility = GeneralUtility::makeInstance(SeoRobotsMetaTagUtility::class);
        foreach ($pages as $index => &$page) {
            $robotsMetaTag = $robotsMetaTagUtility->getSeoRobotsMetaTagValue(
                '',
                ['default' => $this->config['defaultRobotsMetaTag'] ?? ''],
                $page['uid']
            );

            if ((array_flip(SeoRobotsMetaTagUtility::$options)[$robotsMetaTag] ?? 0) > 2) {
                unset($pages[$index]);
            }
        }

        return $pages;
    }
}
