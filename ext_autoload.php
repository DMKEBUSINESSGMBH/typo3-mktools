<?php
/*
 * Register necessary class names with autoloader
 *
 */
return array(
	'tx_mktools_util_errorhandler'							=> t3lib_extMgm::extPath('mktools', 'util/class.tx_mktools_util_ErrorHandler.php'),
	'tx_mktools_util_exceptionhandler'						=> t3lib_extMgm::extPath('mktools', 'util/class.tx_mktools_util_ExceptionHandler.php'),
	'tx_mktools_util_errorexception'						=> t3lib_extMgm::extPath('mktools', 'util/class.tx_mktools_util_ErrorException.php'),
	'tx_mktools_util_misctools'								=> t3lib_extMgm::extPath('mktools', 'util/class.tx_mktools_util_miscTools.php'),
	'tx_mktools_scheduler_generaterealurlconfigurationfile'	=> t3lib_extMgm::extPath('mktools', 'scheduler/class.tx_mktools_scheduler_GenerateRealUrlConfigurationFile.php'),
	'tx_mktools_action_ajax_contentrenderer'				=> t3lib_extMgm::extPath('mktools', 'action/ajax/class.tx_mktools_action_ajax_ContentRenderer.php'),
);
