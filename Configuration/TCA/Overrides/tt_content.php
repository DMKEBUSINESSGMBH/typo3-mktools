<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (tx_mktools_util_miscTools::isAjaxContentRendererActive()) {
    tx_rnbase_util_Extensions::addTCAcolumns(
        'tt_content',
        [
            'tx_mktools_load_with_ajax' => array(
                'exclude' => 1,
                'label' => 'LLL:EXT:mktools/locallang_db.xml:tx_mktools_load_with_ajax',
                'config'  => array(
                    'type'    => 'check',
                    'default' => '0'
                )
            ),
        ],
        !tx_rnbase_util_TYPO3::isTYPO62OrHigher()
    );

    tx_rnbase_util_Extensions::addToAllTCAtypes('tt_content', 'tx_mktools_load_with_ajax', 'list');
}
