<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

return array(
    'ctrl' => array(
        'title' => 'LLL:EXT:mktools/locallang_db.xml:tx_mktools_fixedpostvartypes',
        'label' => 'title',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'enablecolumns' => array(
            'disabled' => 'hidden',
        ),
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'iconfile' => 'EXT:mktools/ext_icon.gif',
    ),
    'interface' => array(
        'showRecordFieldList' => 'hidden,title,identifier',
        'maxDBListItems' => '10'
    ),
    'columns' => array(
        'hidden' => array(
            'exclude' => 0,
            'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config'  => array(
                'type'    => 'check',
                'default' => '0'
            )
        ),
        'title' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:mktools/locallang_db.xml:tx_mktools_fixedpostvartypes.title',
            'config' => array(
                'type' => 'input',
                'size' => '30',
                'max' => '255',
                'eval' => 'required,trim',
            )
        ),
        'identifier' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:mktools/locallang_db.xml:tx_mktools_fixedpostvartypes.identifier',
            'config' => array(
                'type' => 'input',
                'size' => '30',
                'max' => '255',
                'eval' => 'required,trim',
            )
        ),
    ),
    'types' => array(
        '0' => array('showitem' => 'hidden;;1;;1-1-1, title,identifier'
                    )
    ),
    'palettes' => array(
        '1' => array('showitem' => '')
    )
);
