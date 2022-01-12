<?php

if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}

if (tx_mktools_util_miscTools::isSeoRobotsMetaTagActive()) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
        'pages',
        [
            'mkrobotsmetatag' => [
                'exclude' => 1,
                'label' => 'LLL:EXT:mktools/locallang_db.xml:pages.tx_mktools_mkrobotsmetatag',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => tx_mktools_util_SeoRobotsMetaTag::getOptionsForTca(),
                    'size' => 1,
                    'maxitems' => 1,
                    // @see tx_mktools_util_SeoRobotsMetaTag::$options
                    'default' => 0,
                ],
            ],
        ],
        false
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages', 'mkrobotsmetatag', '');
}
