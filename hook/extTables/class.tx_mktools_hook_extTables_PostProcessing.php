<?php
/***************************************************************
 *  Copyright notice
 *
 * (c) 2014 DMK E-BUSINESS GmbH <kontakt@dmk-ebusiness.de>
 * All rights reserved
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

tx_rnbase::load('tx_mktools_util_miscTools');

/**
 * Hook um die TCA zu erweitern
 *
 * @package TYPO3
 * @subpackage tx_mktools
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 * @author Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class tx_mktools_hook_extTables_PostProcessing
	implements \TYPO3\CMS\Core\Database\TableConfigurationPostProcessingHookInterface
{

	/**
	 * Wir erweitern die TCA um einige felder in TYPO3 6.2
	 * Dies machen wir über einen TCA-Hook, um sicherzustellen,
	 * das alle abhängigen extensions bereits geladen wurden.
	 *
	 * @return void
	 */
	public function processData() {
		foreach (tx_mktools_util_miscTools::getTcaPostProcessingExtensions() as $extension) {
			// load only, if extension is loaded!
			if (!tx_rnbase_util_Extensions::isLoaded($extension)) {
				continue;
			}
			// Execute override files from Configuration/TCA/Overrides
			$tcaOverridesPathForPackage = tx_rnbase_util_Extensions::extPath(
				$extension,
				'Configuration/TCA/Overrides'
			);
			if (is_dir($tcaOverridesPathForPackage)) {
				$files = scandir($tcaOverridesPathForPackage);
				foreach ($files as $file) {
					if (
						is_file($tcaOverridesPathForPackage . '/' . $file)
						&& ($file !== '.')
						&& ($file !== '..')
						&& (substr($file, -4, 4) === '.php')
					) {
						require $tcaOverridesPathForPackage . '/' . $file;
					}
				}
			}
		}
	}

}