<?php
defined('TYPO3_MODE') || die('Access denied.');

defined('ERROR_CODE_MKTOOLS') || define('ERROR_CODE_MKTOOLS', 160);

require_once t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_util_TYPO3');

if (!function_exists('mktools_getConf')) {
	function mktools_getConf($key, $mode = FALSE) {
		$extensionConfigurationByKey = tx_mklib_util_MiscTools::getExtensionValue($key, 'mktools');
		return (isset($extensionConfigurationByKey) && ($mode === FALSE || TYPO3_MODE == $mode))
			? $extensionConfigurationByKey : FALSE;
	}
}

if (mktools_getConf('contentReplaceActive', 'FE')) {
	// hook für Content Replace registrieren
	require_once(t3lib_extMgm::extPath('mktools', 'hook/class.tx_mktools_hook_ContentReplace.php'));
	// wenn der scriptmerger installiert ist, muss der replacer wie der scriptmerger aufgerufen werden.
	// der original replacer nutzt pageIndexing, der scripmerger die hooks contentPostProc-all und contentPostProc-output
	if (t3lib_extMgm::isLoaded('scriptmerger')) {
		//@TODO: eine möglichkeit finden, die hooks erst nach dem scriptmerger
		//aufzurufen, ohne die extlist in der localconf anzupassen.
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][]
			= 'EXT:mktools/hook/class.tx_mktools_hook_ContentReplace.php:tx_mktools_hook_ContentReplace->contentPostProcOutput';
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][]
			= 'EXT:mktools/hook/class.tx_mktools_hook_ContentReplace.php:tx_mktools_hook_ContentReplace->contentPostProcAll';
	}
	// der normale weg
	else {
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['pageIndexing'][]
			= 'tx_mktools_hook_ContentReplace';
	}
}

if (mktools_getConf('pageNotFoundHandling', 'FE')) {
	tx_rnbase::load('tx_mktools_util_PageNotFoundHandling');
	tx_mktools_util_PageNotFoundHandling::registerXclass();
}

if (mktools_getConf('realUrlXclass', 'FE')) {
	tx_rnbase::load('tx_mktools_util_RealUrl');
	tx_mktools_util_RealUrl::registerXclass();
}

require(t3lib_extMgm::extPath('mktools').'scheduler/ext_localconf.php');

// es wird eine Warnung erzeugt wenn für einen Link Wizard nicht "params" in der TCA konfiguriert
// ist, da das dann als string statt wie erwartet als array übergeben wird
tx_rnbase::load('tx_rnbase_util_TYPO3');
if (!tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/browse_links.php']['browserRendering'][]
		= 'EXT:mktools/hook/class.tx_mktools_hook_BrowseLinks.php:tx_mktools_hook_BrowseLinks';
}

tx_rnbase::load('tx_mktools_util_miscTools');
$tcaPostProcessingExtensions = tx_mktools_util_miscTools::getTcaPostProcessingExtensions();
if (
	tx_rnbase_util_TYPO3::isTYPO62OrHigher() &&
	!empty($tcaPostProcessingExtensions)
) {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing'][]
		= 'EXT:mktools/hook/extTables/class.tx_mktools_hook_extTables_PostProcessing.php:tx_mktools_hook_extTables_PostProcessing';
}

if (mktools_getConf('systemLogLockThreshold')) {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog'][]
		= 'EXT:mktools/hook/class.tx_mktools_hook_GeneralUtility.php:tx_mktools_hook_GeneralUtility->preventSystemLogFlood';
}

// Robots-Meta Tag
if(tx_mktools_util_miscTools::isSeoRobotsMetaTagActive()) {
	$GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] .= ',mkrobotsmetatag';
}
