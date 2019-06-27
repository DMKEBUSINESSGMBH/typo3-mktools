<?php

/**
 * Utilities zum Laden von Typo3 Resourcen.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mktools_util_T3Loader
{
    /**
     * @var array[TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer]
     */
    private static $cObj = array();

    /**
     * @param int $contentId
     *
     * @return TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer or tslib_cObj
     */
    public static function getContentObject($contentId = 0)
    {
        $contentObjectRendererClass = tx_rnbase_util_Typo3Classes::getContentObjectRendererClass();
        if (!self::$cObj[$contentId] instanceof $contentObjectRendererClass) {
            self::$cObj[$contentId] = tx_rnbase::makeInstance($contentObjectRendererClass);
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
        return tx_rnbase_util_TYPO3::getSysPage();
    }
}
