<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (tx_mktools_util_miscTools::loadFixedPostVarTypesTable()) {
    // pages erweitern
    tx_rnbase_util_TCA::loadTCA('pages');
    tx_rnbase_util_Extensions::addTCAcolumns(
        'pages',
        array(
            'tx_mktools_fixedpostvartype' => array(
                'exclude' => 1,
                'label' => 'LLL:EXT:mktools/locallang_db.xml:tx_mktools_fixedpostvartype',
                'config' => array(
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => array(
                        array('LLL:EXT:mktools/locallang_db.xml:general.choose', ''),
                    ),
                    'foreign_table' => 'tx_mktools_fixedpostvartypes',
                    'foreign_table_where' => ' ORDER BY tx_mktools_fixedpostvartypes.title',
                    'size' => 1,
                    'minitems' => 0,
                    'maxitems' => 1,
                ),
            ),
        ),
        false
    );

    tx_rnbase_util_Extensions::addToAllTCAtypes('pages', 'tx_mktools_fixedpostvartype', '');
}

if (tx_mktools_util_miscTools::isSeoRobotsMetaTagActive()) {
    // pages erweitern
    tx_rnbase_util_TCA::loadTCA('pages');
    tx_rnbase_util_Extensions::addTCAcolumns(
        'pages',
        array(
            'mkrobotsmetatag' => array(
                'exclude' => 1,
                'label' => 'LLL:EXT:mktools/locallang_db.xml:pages.tx_mktools_mkrobotsmetatag',
                'config' => array(
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => tx_mktools_util_SeoRobotsMetaTag::getOptionsForTca(),
                    'size' => 1,
                    'maxitems' => 1,
                    // @see tx_mktools_util_SeoRobotsMetaTag::$options
                    'default' => 0,
                ),
            ),
        ),
        false
    );
    tx_rnbase_util_Extensions::addToAllTCAtypes('pages', 'mkrobotsmetatag', '');
}
