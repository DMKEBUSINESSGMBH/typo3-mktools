<?php

return [
    'tx_mktools_action_ShowTemplate' => \DMK\Mktools\Action\ShowTemplateAction::class,
    'tx_mktools_action_DocMarkDown' => \DMK\Mktools\Action\MarkdownAction::class,
    'tx_mktools_action_FlashMessage' => \DMK\Mktools\Action\FlashMessageAction::class,
    'tx_mktools_action_TsLib' => \DMK\Mktools\Action\TyposcriptLibraryAction::class,
    'tx_mktools_action_ajax_ContentRenderer' => \DMK\Mktools\Action\Ajax\ContentRendererAction::class,
    'tx_mktools_hook_ContentReplace' => \DMK\Mktools\Hook\ContentReplaceHook::class,
    'tx_mktools_hook_GeneralUtility' => \DMK\Mktools\Hook\GeneralUtilityHook::class,
    'Tx_Mktools_Cli_FindUnusedLocallangLabels' => \DMK\Mktools\Cli\FindUnusedLocallangLabels::class,

    'tx_mktools_model_Pages' => \DMK\Mktools\Model\Page::class,
    'tx_mktools_model_FixedPostVarType' => \DMK\Mktools\Model\FixedPostVarType::class,
    'tx_mktools_scheduler_GenerateRealUrlConfigurationFile' => \DMK\Mktools\Scheduler\GenerateRealUrlConfigurationFileTask::class,
    'tx_mktools_view_ShowTemplate' => \DMK\Mktools\View\ShowTemplate::class,
    'tx_mktools_util_Composer' => \DMK\Mktools\Utility\ComposerUtility::class,
    'tx_mktools_util_RealUrl' => \DMK\Mktools\Utility\RealUrlUtility::class,
    'tx_mktools_util_miscTools' => \DMK\Mktools\Utility\Misc::class,
    'tx_mktools_util_T3Loader' => \DMK\Mktools\Utility\T3Loader::class,
    'tx_mktools_util_ErrorException' => \DMK\Mktools\Exception\RuntimeException::class,
    'tx_mktools_util_ErrorHandler' => \DMK\Mktools\ErrorHandler\ErrorHandler::class,
    'tx_mktools_util_ExceptionHandlerBase' => \DMK\Mktools\ErrorHandler\ExceptionHandler::class,
    'tx_mktools_util_ExceptionHandler' => \DMK\Mktools\ErrorHandler\ThrowableExceptionHandler::class,
    'tx_mktools_util_PageNotFoundHandling' => \DMK\Mktools\ErrorHandler\PageNotFoundHandler::class,
    'tx_mktools_util_FlashMessage' => \DMK\Mktools\Session\FlashMessageStorage::class,
    'tx_mktools_util_SeoRobotsMetaTag' => \DMK\Mktools\Utility\SeoRobotsMetaTagUtility::class,
];
