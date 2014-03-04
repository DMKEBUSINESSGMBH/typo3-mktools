<?php
/**
 * @package Tx_Mktools
 * @subpackage Tx_Mktools_Util
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
 * @package tx_mktools
 * @subpackage tx_mktools_util
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
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
	 * Initialize typo3 stuff. If an optional content element
	 * id is given, the flexform data is initialized as well.
	 *
	 * @param int $contentId Content element id
	 *
	 * @return tslib_cObj
	 */
	public static function tsfe($pageId = 0, $contentId = null) {
		/* @var $TSFE tslib_fe */
		global $TSFE;

		include_once PATH_t3lib . 'class.t3lib_page.php';
		include_once PATH_t3lib . 'class.t3lib_userauth.php';
		include_once PATH_tslib . 'class.tslib_fe.php';
		include_once PATH_tslib . 'class.tslib_feuserauth.php';
		include_once PATH_tslib . 'class.tslib_content.php';

		if (!$TSFE instanceof tslib_fe) {

			tslib_eidtools::connectDB();
			tslib_eidtools::initTCA();
// 			tslib_eidtools::initExtensionTCA('tt_news');

			$TSFE = t3lib_div::makeInstance(
				'tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], $pageId, 0
			);

			$TSFE->sys_page = self::getSysPage();

			if (!self::$bUseCookies) {
				$TSFE->fe_user->dontSetCookie = true;
				$TSFE->TYPO3_CONF_VARS['FE']['dontSetCookie'] = true;
			}

			// Initialize frontend user object
			$TSFE->connectToDB();
			$TSFE->initFEuser();
			$TSFE->determineId();
			$TSFE->config['config']['language'] = null;//pibase constructor
			// sets $TSFE->loginUser
			$TSFE->initUserGroups();
			$TSFE->getCompressedTCarray();

		}

		self::BEUser();

		// Dummy config
		$TSFE->config = array('config' => array());

		if ($contentId === null) {
			return;
		}

		$TSFE->initTemplate();

		// Create content object
		$cObj = self::getContentObject($contentId);

		// Load content element from database
		$cObj->data = $TSFE->sys_page->checkRecord(
			'tt_content', $contentId
		);

		// Page id
		$TSFE->id = (int) $cObj->data['pid'];

		// Get rootline and page config
		$TSFE->getPageAndRootline();
		$TSFE->getConfigArray();

		return $cObj;
	}

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
