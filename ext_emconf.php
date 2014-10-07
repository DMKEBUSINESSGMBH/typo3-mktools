<?php
/**
 * Extension Manager/Repository config file for ext "mklib".
 * @package tx_mktools
 * @subpackage tx_mktools_
 */

########################################################################
# Extension Manager/Repository config file for ext "mktools".
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'MK Tools',
	'description' => 'Collection of some small tools',
	'category' => 'misc',
	'author' => 'das MedienKombinat GmbH',
	'author_email' => 'kontakt@das-medienkombinat.de',
	'author_company' => 'das Medienkombinat GmbH',
	'shy' => '',
	'dependencies' => 'rn_base, mklib',
	'version' => '0.4.6',
	'conflicts' => '',
	'priority' => '',
	'createDirs' => 'typo3temp/mktools/locks/',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'constraints' => array(
		'depends' => array(
 			'rn_base' => '',
			'mklib' => '',
			'typo3' => '4.3.0-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);