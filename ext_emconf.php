<?php
/**
 * Extension Manager/Repository config file for ext "mklib".
 * @package TYPO3
 * @subpackage tx_mktools
 */

//
// Extension Manager/Repository config file for ext "mktools".
//
// Manual updates:
// Only the data in the array - everything else is removed by next
// writing. "version" and "dependencies" must not be touched!
//
$EM_CONF[$_EXTKEY] = array(
    'title' => 'MK Tools',
    'description' => 'Collection of some useful tools',
    'category' => 'misc',
    'author' => 'DMK E-Business GmbH',
    'author_email' => 'dev@dmk-ebusiness.de',
    'author_company' => 'DMK E-Business GmbH',
    'shy' => '',
    'dependencies' => 'rn_base, mklib',
    'version' => '3.0.31',
    'conflicts' => '',
    'priority' => '',
    'createDirs' => 'typo3temp/mktools/locks/',
    'module' => '',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => 0,
    'modify_tables' => '',
    'clearCacheOnLoad' => 0,
    'lockType' => '',
    'constraints' => array(
        'depends' => array(
            'rn_base' => '1.4.0-',
            'typo3' => '4.5.0-8.7.99',
            'mklib' => '3.0.0-'
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
);
