<?php

namespace DMK\Mktools\Utility;

/***************************************************************
 *  Copyright notice
 *
 * (c) 2021 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
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
 ***************************************************************/

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Utilities zum Laden von Typo3 Resourcen.
 *
 * @author Michael Wagner
 */
final class T3Loader
{
    /**
     * @var array[TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer]
     */
    private static $cObj = [];

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * @param int $contentId
     *
     * @return ContentObjectRenderer
     */
    public static function getContentObject($contentId = 0)
    {
        $contentObjectRendererClass = \tx_rnbase_util_Typo3Classes::getContentObjectRendererClass();

        if (!self::$cObj[$contentId] instanceof $contentObjectRendererClass) {
            self::$cObj[$contentId] = \tx_rnbase::makeInstance($contentObjectRendererClass);
        }

        return self::$cObj[$contentId];
    }

    /**
     * @return \TYPO3\CMS\Frontend\Page\PageRepository or t3lib_pageSelect
     *
     * @deprecated use tx_rnbase_util_TYPO3::getSysPage() instead. will be removed soon.
     */
    public static function getSysPage()
    {
        return \tx_rnbase_util_TYPO3::getSysPage();
    }
}
