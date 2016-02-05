<?php
/*
 * Register necessary class names with autoloader
 *
 */
// wir können weder den Extensionmanager nutzen (auf Grund der verschiedenen TYPO3 Versionen)
// noch tx_rnbase_util_Extensions (weil noch nicht geladen). Also nehmen wir den Pfad hart. Dürfte
// aber kein Problem sein.
$extensionPath = PATH_typo3conf . 'ext/mktools/';
return array(
	'tx_mktools_util_errorhandler'							=> $extensionPath . 'util/class.tx_mktools_util_ErrorHandler.php',
	'tx_mktools_util_exceptionhandler'						=> $extensionPath . 'util/class.tx_mktools_util_ExceptionHandler.php',
	'tx_mktools_util_errorexception'						=> $extensionPath . 'util/class.tx_mktools_util_ErrorException.php',
	'tx_mktools_util_misctools'								=> $extensionPath . 'util/class.tx_mktools_util_miscTools.php',
	'tx_mktools_scheduler_generaterealurlconfigurationfile'	=> $extensionPath . 'scheduler/class.tx_mktools_scheduler_GenerateRealUrlConfigurationFile.php',
	'tx_mktools_action_ajax_contentrenderer'				=> $extensionPath . 'action/ajax/class.tx_mktools_action_ajax_ContentRenderer.php'
);
