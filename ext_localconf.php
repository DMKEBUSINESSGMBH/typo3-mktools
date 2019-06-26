<?php
use DMK\Mktools\ContentObject\UserInternalContentObject;

defined('TYPO3_MODE') || die('Access denied.');

defined('ERROR_CODE_MKTOOLS') || define('ERROR_CODE_MKTOOLS', 160);



if (!function_exists('mktools_getConf')) {
    function mktools_getConf($key, $mode = false)
    {
        $extensionConfigurationByKey = tx_mklib_util_MiscTools::getExtensionValue($key, 'mktools');

        return (isset($extensionConfigurationByKey) && ($mode === false || TYPO3_MODE == $mode)) ? $extensionConfigurationByKey : false;
    }
}

if (mktools_getConf('contentReplaceActive', 'FE')) {
    // hook für Content Replace registrieren
    require_once(tx_rnbase_util_Extensions::extPath('mktools', 'hook/class.tx_mktools_hook_ContentReplace.php'));
    // wenn der scriptmerger installiert ist, muss der replacer wie der scriptmerger aufgerufen werden.
    // der original replacer nutzt pageIndexing, der scripmerger die hooks contentPostProc-all und contentPostProc-output
    if (tx_rnbase_util_Extensions::isLoaded('scriptmerger')) {
        //@TODO: eine möglichkeit finden, die hooks erst nach dem scriptmerger
        //aufzurufen, ohne die extlist in der localconf anzupassen.
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][]
            = 'tx_mktools_hook_ContentReplace->contentPostProcOutput';
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][]
            = 'tx_mktools_hook_ContentReplace->contentPostProcAll';
    } // der normale weg
    else {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['pageIndexing'][]
            = 'tx_mktools_hook_ContentReplace';
    }
}

if (!tx_rnbase_util_TYPO3::isTYPO90OrHigher() && mktools_getConf('pageNotFoundHandling', 'FE')) {
    tx_mktools_util_PageNotFoundHandling::registerXclass();
}

if (mktools_getConf('realUrlXclass', 'FE') && !tx_rnbase_util_TYPO3::isTYPO90OrHigher()) {
    tx_mktools_util_RealUrl::registerXclass();
}

require(tx_rnbase_util_Extensions::extPath('mktools').'scheduler/ext_localconf.php');

if (mktools_getConf('systemLogLockThreshold')) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog'][]
        = 'tx_mktools_hook_GeneralUtility->preventSystemLogFlood';
}

// Robots-Meta Tag
if (tx_mktools_util_miscTools::isSeoRobotsMetaTagActive()) {
    $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] .= ',mkrobotsmetatag';
}

if (TYPO3_MODE == 'BE' && !tx_rnbase_util_TYPO3::isTYPO90OrHigher()) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['mktools_find_unused_locallang_labels'] =
        array('EXT:mktools/Classes/Cli/FindUnusedLocallangLabels.php','_CLI_mktools_find_unused_locallang_labels');
}

if (tx_mktools_util_miscTools::getExceptionPage()) {
    // wenn wir eine Exception Page haben, wird wohl auch das Exception Handling mit mktools erledigt.
    // In diesem Fall soll das Exception Handling von Content Objects deaktiviert werden.
    tx_rnbase_util_Extensions::addTypoScript('mktools', 'setup', 'config.contentObjectExceptionHandler = 0');
}

// piwa is often used for piwik custom variables
Tx_Rnbase_Utility_Cache::addExcludedParametersForCacheHash(array(
    'piwa',
));

if (tx_mktools_util_miscTools::isAjaxContentRendererActive()) {
    $GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects']['USER_INT'] = UserInternalContentObject::class;
}
