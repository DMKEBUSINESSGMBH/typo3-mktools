<?php
/**
 * 	@package TYPO3
 *  @subpackage tx_mktools
 *
 *  Copyright notice
 *
 *  (c) 2015 DMk E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
 */

/**
 * es wird eine Warnung erzeugt wenn für einen Link Wizard nicht "params" in der TCA konfiguriert
 * ist, da das dann als string statt wie erwartet als array übergeben wird
 *
 * @author	Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @package	TYPO3
 * @subpackage tx_mktools
 */
class tx_mktools_hook_BrowseLinks {

	/**
	 * @param string $mode
	 * @param mixed (e.g. can be SC_browse_links or tx_rtehtmlarea_SC_browse_links) $parentObject
	 */
	public function isValid($mode, $parentObject) {
		// params muss array sein damit die Warnung nicht erzeugt wird
		if (is_array($_GET['P']) && !is_array($_GET['P']['params'])) {
			$_GET['P']['params'] = array();
		}

		// wir geben immer FALSE zurück damit nicht render im Hook aufgerufen wird
		return FALSE;
	}

	/**
	 * nichts zu tun. muss nur vorhanden sein, wird aber nie aufgerufen wenn isValid FALSE
	 * liefert
	 *
	 * @param string $mode
	 * @param mixed (e.g. can be SC_browse_links or tx_rtehtmlarea_SC_browse_links) $parentObject
	 */
	public function render($mode, $parentObject) {
	}
}