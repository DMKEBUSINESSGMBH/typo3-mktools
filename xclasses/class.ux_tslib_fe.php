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
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
 */
class ux_tslib_fe extends tslib_fe {


	/**
	 * Page-not-found handler for use in frontend plugins from extensions.
	 *
	 * @param	string		Reason text
	 * @param	string		HTTP header to send
	 * @return	void		Function exits.
	 */
	function pageNotFoundAndExit($reason='', $header='')
	{

		// wir prüfen erstmal dne pageNotFound_handling wert auf mktools konfiguration
		$code = $this->TYPO3_CONF_VARS['FE']['pageNotFound_handling'];
		if (t3lib_div::isFirstPartOfStr($code, 'MKTOOLS_'))
		{
			tx_rnbase::load('tx_mktools_util_PageNotFoundHandling');
			tx_mktools_util_PageNotFoundHandling::getInstance($this, $reason, $header)
				->handlePageNotFound($code);
		}

		// ohne mktools abbrechen und einfach weiter rendern:
		//   wenn pageNotFound 1 (ID was not an accessible page)
		//   oder pageNotFound 2 (Subsection was found and not accessible)
		//   Wenn das nicht gewünscht ist, die XCLASS einfach deaktivieren!
		//     (pageNotFoundHandling in der ExtConf)
		if($this->pageNotFound === 1 || $this->pageNotFound === 2)
		{
			return;
		}
 		// else
		parent::pageNotFoundAndExit($reason, $header);
	}
}
if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']
		['ext/mktools/xclasses/class.ux_tslib_fe.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']
		['ext/mktools/xclasses/class.ux_tslib_fe.php']);
}