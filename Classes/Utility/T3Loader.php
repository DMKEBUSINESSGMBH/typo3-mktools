<?php

namespace DMK\Mktools\Utility;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Utilities zum Laden von Typo3 Resourcen.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
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
