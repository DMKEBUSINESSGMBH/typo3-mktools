<?php
/**
 * @package TYPO3
 * @subpackage tx_mktools
 *
 * Copyright notice
 *
 * (c) 2014 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

/**
 * Utilities zum Laden von Typo3 Resourcen
 *
 * @package TYPO3
 * @subpackage tx_mktools
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mktools_util_T3Loader
{


	/**
	 * Control set fe session cookie (shold be false in unittests)
	 *
	 * @var boolean
	 */
	public static $bUseCookies = true;

	private static $cObj = array();

	/**
	 *
	 * @param int $contentId
	 * @return tslib_cObj
	 */
	public static function getContentObject($contentId = 0) {
		if (!self::$cObj[$contentId] instanceof tslib_cObj) {
			self::$cObj[$contentId] = t3lib_div::makeInstance('tslib_cObj');
		}
		return self::$cObj[$contentId];
	}

	/**
	 * @return t3lib_pageSelect
	 */
	public static function getSysPage() {
		/* @var $TSFE tslib_fe */
		global $TSFE;

		static $syspage = null;

		if (!$syspage) {
			if ($TSFE instanceof tslib_fe
					&& $TSFE->sys_page instanceof t3lib_pageSelect) {
				$syspage = $TSFE->sys_page;
			}
			$syspage = t3lib_div::makeInstance('t3lib_pageSelect');
		}

		return $syspage;
	}

}
