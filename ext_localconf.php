<?php
defined('TYPO3_MODE') || die('Access denied.');

defined('ERROR_CODE_MKADVERT') || define('ERROR_CODE_MKTOOLS', 160);

$_EXTKEY = 'mktools';
$_EXTCONF = unserialize($_EXTCONF);

if (!function_exists('mktools_getConf')) {
	function mktools_getConf($key, $mode = false) {
		global $_EXTCONF;
		return (isset($_EXTCONF[$key]) && ($mode === false || TYPO3_MODE == $mode))
			? $_EXTCONF[$key] : false;
	}
}

if (mktools_getConf('contentReplaceActive', 'FE')) {
	// hook für Content Replace registrieren
	require_once(t3lib_extMgm::extPath('mktools', 'hook/class.tx_mktools_hook_ContentReplace.php'));
	// wenn der scriptmerger installiert ist, muss der replacer wie der scriptmerger aufgerufen werden.
	// der original replacer nutzt pageIndexing, der scripmerger die hooks contentPostProc-all und contentPostProc-output
	if (t3lib_extMgm::isLoaded('scriptmerger')) {
		//@TODO: eine möglichkeit finden, die hooks erst nach dem scriptmerger aufzurufen, ohne die extlist in der localconf anzupassen.
		$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][]
			= 'EXT:mktools/hook/class.tx_mktools_hook_ContentReplace.php:tx_mktools_hook_ContentReplace->contentPostProcOutput';
		$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][]
			= 'EXT:mktools/hook/class.tx_mktools_hook_ContentReplace.php:tx_mktools_hook_ContentReplace->contentPostProcAll';
	}
	// der normale weg
	else {
		$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['pageIndexing'][]
			= 'tx_mktools_hook_ContentReplace';
	}
}

if (mktools_getConf('pageNotFoundHandling', 'FE')) {
	// Anpassung tslib_fe für 404
	$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tslib/class.tslib_fe.php']
		= t3lib_extMgm::extPath($_EXTKEY).'xclasses/class.ux_tslib_fe.php';
}

require(t3lib_extMgm::extPath($_EXTKEY).'scheduler/ext_localconf.php');
