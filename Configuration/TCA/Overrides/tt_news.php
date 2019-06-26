<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (!tx_mktools_util_miscTools::shouldFalImagesBeAddedToTtNews()) {
    return;
}
tx_rnbase_util_TCA::loadTCA('tt_news');
tx_rnbase_util_Extensions::addTCAcolumns(
    'tt_news',
    array(
        'tx_mktools_fal_images' => tx_rnbase_util_TSFAL::getMediaTCA(
            'tx_mktools_fal_images',
            array(
                'exclude' => 1,
                'label' => 'LLL:EXT:mktools/locallang_db.xml:tx_mktools_fal_images',
            )
        ),
        'tx_mktools_fal_media' => tx_rnbase_util_TSFAL::getMediaTCA(
            'tx_mktools_fal_media',
            array(
                'exclude' => 1,
                'label' => 'LLL:EXT:mktools/locallang_db.xml:tx_mktools_fal_media',
                'type' => 'media',
            )
        ),
    ),
    false
);

tx_rnbase_util_Extensions::addToAllTCAtypes(
    'tt_news',
    'tx_mktools_fal_images,tx_mktools_fal_media',
    '',
    'after:image'
);
