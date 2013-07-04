<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 das Medienkombinat <kontakt@das-medienkombinat.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

/**
 * Miscellaneous common methods
 */
class tx_mktools_util_miscTools {
	
	/**
	 * Get fields to expand
	 *
	 * @return int
	 */
	private function getExtensionCfgValue($configValue){
		tx_rnbase::load('tx_rnbase_configurations');
		return tx_rnbase_configurations::getExtensionCfgValue('mktools', $configValue);
	}
	
	public function isSeoRobotsMetaTagActive() {
		return self::getExtensionCfgValue('seoRobotsMetaTagActive');
	}
	
	public function isContentReplacerActive() {
		return self::getExtensionCfgValue('contentReplaceActive');
	}
	
	public function pageNotFoundHandlingActive() {
		return self::getExtensionCfgValue('pageNotFoundHandling');
	}
	
	public function getErrorPage() {
		return self::getExtensionCfgValue('errorPage');
	}

	/**
	 * @param string $staticPath
	 * @param string $additionalPath
	 * @return 	tx_rnbase_configurations
	 */
	public static function getConfirgurations($staticPath, $additionalPath=''){
		t3lib_extMgm::addPageTSConfig(
			'<INCLUDE_TYPOSCRIPT: source="FILE:'.$staticPath.'">');
		if (!empty($additionalPath)) {
			t3lib_extMgm::addPageTSConfig(
				'<INCLUDE_TYPOSCRIPT: source="FILE:'.$additionalPath.'">'
			);
		}
		
		$pageTSconfig = t3lib_BEfunc::getPagesTSconfig(0);
		$config = t3lib_div::array_merge_recursive_overrule(
			(array) $pageTSconfig['config.']['tx_mktools.'], 
			(array) $pageTSconfig['plugin.']['tx_mktools.']
		);
			
		$configurations = new tx_rnbase_configurations();
	    $configurations->init($config, $configurations->getCObj(), 'mktools', 'mktools');
		$configurations->setParameters(
			tx_rnbase::makeInstance('tx_rnbase_parameters')
		);
		
		return $configurations;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mktools/util/class.tx_mktools_util_miscTools.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mktools/util/class.tx_mktools_util_miscTools.php']);
}