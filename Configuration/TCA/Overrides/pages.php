<?php

if (!defined('TYPO3')) {
    exit('Access denied.');
}

if (\DMK\Mktools\Utility\Misc::isSeoRobotsMetaTagActive()) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
        'pages',
        [
            'mkrobotsmetatag' => [
                'exclude' => 1,
                'label' => 'LLL:EXT:mktools/Resources/Private/Language/locallang_db.xlf:pages.tx_mktools_mkrobotsmetatag',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => \DMK\Mktools\Utility\SeoRobotsMetaTagUtility::getOptionsForTca(),
                    'size' => 1,
                    'maxitems' => 1,
                    // @see SeoRobotsMetaTagUtility::$options
                    'default' => 0,
                ],
            ],
        ],
        false
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages', 'mkrobotsmetatag', '');
}
