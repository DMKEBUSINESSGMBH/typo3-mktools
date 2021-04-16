<?php

use DMK\Mktools\ContentObject\UserContentObject;
use DMK\Mktools\ContentObject\UserInternalContentObject;

defined('TYPO3_MODE') || exit('Access denied.');

defined('ERROR_CODE_MKTOOLS') || define('ERROR_CODE_MKTOOLS', 160);

if (!function_exists('mktools_getConf')) {
    function mktools_getConf($key, $mode = false)
    {
        $extensionConfigurationByKey = tx_rnbase_configurations::getExtensionCfgValue('mktools', $key);

        return (isset($extensionConfigurationByKey) && (false === $mode || TYPO3_MODE == $mode)) ? $extensionConfigurationByKey : false;
    }
}

if (mktools_getConf('contentReplaceActive', 'FE')) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][]
        = 'tx_mktools_hook_ContentReplace->contentPostProcAll';
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][]
        = 'tx_mktools_hook_ContentReplace->contentPostProcOutput';
}

if (mktools_getConf('systemLogLockThreshold')) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['systemLog'][]
        = 'tx_mktools_hook_GeneralUtility->preventSystemLogFlood';
}

// Robots-Meta Tag
if (tx_mktools_util_miscTools::isSeoRobotsMetaTagActive()) {
    $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] .= ',mkrobotsmetatag';
}

if (tx_mktools_util_miscTools::getExceptionPage()) {
    // wenn wir eine Exception Page haben, wird wohl auch das Exception Handling mit mktools erledigt.
    // In diesem Fall soll das Exception Handling von Content Objects deaktiviert werden.
    tx_rnbase_util_Extensions::addTypoScript('mktools', 'setup', 'config.contentObjectExceptionHandler = 0');
}

// piwa is often used for piwik custom variables
Tx_Rnbase_Utility_Cache::addExcludedParametersForCacheHash([
    'piwa',
]);

if (tx_mktools_util_miscTools::isAjaxContentRendererActive()) {
    $GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects']['USER_INT'] = UserInternalContentObject::class;
    $GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects']['USER'] = UserContentObject::class;
}
