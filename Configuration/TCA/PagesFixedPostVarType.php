<?php
if (!defined ('TYPO3_MODE')) { die ('Access denied.'); }

require_once(t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php'));
tx_rnbase::load('tx_mktools_util_SeoRobotsMetaTag');

// pages erweitern
t3lib_div::loadTCA('pages');
$fields = array(
	'tx_mktools_fixedpostvartype' => array (
        'exclude' => 1,
        'label' => 'LLL:EXT:mktools/locallang_db.xml:tx_mktools_fixedpostvartype',
        'config' => array (
			'type' => 'select',
			'items' => array (
				array('LLL:EXT:mktools/locallang_db.xml:general.choose', '')
			),
			'foreign_table' => 'tx_mktools_fixedpostvartypes',
        	'foreign_table_where' => ' ORDER BY title',
			'size' => 1,
			'minitems' => 0,
 	 		'maxitems' => 1
		)
	),
);
t3lib_extMgm::addTCAcolumns('pages', $fields, 1);
t3lib_extMgm::addToAllTCAtypes('pages','tx_mktools_fixedpostvartype','');
