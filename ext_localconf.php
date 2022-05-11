<?php

use DMK\Mktools\ContentObject\UserContentObject;
use DMK\Mktools\ContentObject\UserInternalContentObject;
use DMK\Mktools\Utility\Misc;

defined('TYPO3') || exit('Access denied.');

defined('ERROR_CODE_MKTOOLS') || define('ERROR_CODE_MKTOOLS', 160);

if (Misc::isContentReplacerActive()) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][]
        = \DMK\Mktools\Hook\ContentReplaceHook::class.'->contentPostProcAll';
}

// Robots-Meta Tag
if (\DMK\Mktools\Utility\Misc::isSeoRobotsMetaTagActive()) {
    $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] .= ',mkrobotsmetatag';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
        TCEFORM.pages{
            no_index.disabled = 1
            no_follow.disabled = 1
        }
    ');
}

if (\DMK\Mktools\Utility\Misc::getExceptionPage()) {
    // wenn wir eine Exception Page haben, wird wohl auch das Exception Handling mit mktools erledigt.
    // In diesem Fall soll das Exception Handling von Content Objects deaktiviert werden.
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript('mktools', 'setup', 'config.contentObjectExceptionHandler = 0');
}

// piwa is often used for piwik custom variables
\Sys25\RnBase\Utility\CHashUtility::addExcludedParametersForCacheHash([
    'piwa',
    // In case ajax requests are done with GET we need to exclude those parameters as they are added on the fly
    // when clicking a link. Therefore they are not present when calculating the cHash.
    'contentid',
    'href',
    'mktoolsAjaxRequest',
    'page',
    'requestType',
    'useHistory',
]);

if (\DMK\Mktools\Utility\Misc::isAjaxContentRendererActive()) {
    $GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects']['USER_INT'] = UserInternalContentObject::class;
    $GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects']['USER'] = UserContentObject::class;
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['oldMktoolsPluginsMigration']
    = \DMK\Mktools\Updates\MigrateOldMktoolsPlugins::class;

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('mksanitizedparameters')) {
    \DMK\MkSanitizedParameters\Rules::addRulesForFrontend(['href' => FILTER_SANITIZE_URL]);
}
