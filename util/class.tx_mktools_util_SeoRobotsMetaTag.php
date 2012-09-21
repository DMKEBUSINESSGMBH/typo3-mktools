<?php
/*  **********************************************************************  **
 *  Copyright notice
 *
 *  (c) 2012 das MedienKombinat GmbH <kontakt@das-medienkombinat.de>
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
 *  ***********************************************************************  */
require_once(t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php'));

/**
 *
 * @package tx_mktools
 * @author Christian Riesche <christian.riesche@das-medienkombinat.de>
 */
class tx_mktools_util_SeoRobotsMetaTag {

	/**
	 * Werte für das robots Meta Tag
	 * @var array
	 */
	public static $options = array(
		0 => 'by TS',
		1 => 'INDEX,FOLLOW',
		2 => 'INDEX,NOFOLLOW',
		3 => 'NOINDEX,FOLLOW',
		4 => 'NOINDEX,NOFOLLOW',
		5 => 'NOODP,NOINDEX,FOLLOW',
	);
	

	/**
	 * Formattierte Ausgabe der Werte für das TCA
	 * @return array
	 */
	public static function getOptionsForTCA() {
 		$tcaOptions = array();
 		foreach (self::$options as $key => $option) {
 			$tcaOptions[] = array($option, $key);
 		}
 		return $tcaOptions;
	}
	
	/**
	 * Gibt passenden Wert des Robots Tag zurück
	 * @param int $key
	 * @return string
	 */
	private function getOptionByValue($key) {
		if(array_key_exists($key, self::$options)) {
			return self::$options[$key];
		}
		return '';
	}
	
	/**
	 * Liefert den Wert des für diese Seite relevanten Robots Meta Tag
	 * zurück. Wird keiner gefunden, dann wird als Default der Wert der
	 * Konstanten {$config.tx_mktools.seorobotsmetatag.default} zurückgegeben
	 *
	 * @param string $sContent
	 * @param array $aConfig
	 * @return string
	 */
	public function getSeoRobotsMetaTagValue($sContent = '', array $aConfig = array()) 	{
		$robotsValue = $this->getRobotsValue();
		if ($robotsValue > 0) {
			return $this->getOptionByValue($robotsValue);
		}
		return $aConfig['default'];
	}


	/**
	 * Sucht rekursiv von der aktuellen Seite aus, ob ein
	 * Wert für ein individuelles Robots-Tag gesetzt ist
	 * @return int
	 */
	private function getRobotsValue() 	{
		$sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		$rootLine = $sys_page->getRootLine($GLOBALS['TSFE']->id);
		foreach ($rootLine as $curPage) {
			$options['where'] = 'uid = \''.$curPage['uid'].'\' AND mkrobotsmetatag > \'0\'';
			$results = tx_rnbase_util_DB::doSelect('*', 'pages', $options);
			if (!empty($results))
				return $results[0]['mkrobotsmetatag'];
		}
		return 0;
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']
		['ext/mktools/util/class.tx_mktools_util_SeoRobotsMetaTag.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']
		['ext/mktools/util/tx_mktools_util_SeoRobotsMetaTag.php']);
}
