<?php

if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}

if (!tx_mktools_util_miscTools::shouldFalImagesBeAddedToTtNews()) {
    return;
}
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'tt_news',
    [
        'tx_mktools_fal_images' => \Sys25\RnBase\Utility\TSFAL::getMediaTCA(
            'tx_mktools_fal_images',
            [
                'exclude' => 1,
                'label' => 'LLL:EXT:mktools/locallang_db.xml:tx_mktools_fal_images',
            ]
        ),
        'tx_mktools_fal_media' => \Sys25\RnBase\Utility\TSFAL::getMediaTCA(
            'tx_mktools_fal_media',
            [
                'exclude' => 1,
                'label' => 'LLL:EXT:mktools/locallang_db.xml:tx_mktools_fal_media',
                'type' => 'media',
            ]
        ),
    ],
    false
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tt_news',
    'tx_mktools_fal_images,tx_mktools_fal_media',
    '',
    'after:image'
);
