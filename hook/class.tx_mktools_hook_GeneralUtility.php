<?php
/**
 * 	@package TYPO3
 *	@subpackage tx_mktools
 *
 *	Copyright notice
 *
 *	(c) 2011 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 *	All rights reserved
 *
 *	This script is part of the TYPO3 project. The TYPO3 project is
 *	free software; you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation; either version 2 of the License, or
 *	(at your option) any later version.
 *
 *	The GNU General Public License can be found at
 *	http://www.gnu.org/copyleft/gpl.html.
 *
 *	This script is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
 *	GNU General Public License for more details.
 *
 *	This copyright notice MUST APPEAR in all copies of the script!
 */
tx_rnbase::load('tx_rnbase_util_Lock');
tx_rnbase::load('tx_rnbase_util_Lock');

/**
 * tx_mktools_hook_GeneralUtility
 *
 * @package 		TYPO3
 * @subpackage	 	mktools
 * @author 			Hannes Bochmann <dev@dmk-ebusiness.de>
 * @license 		http://www.gnu.org/licenses/lgpl.html
 * 					GNU Lesser General Public License, version 3 or later
 */
class tx_mktools_hook_GeneralUtility {

	/**
	 * @var string
	 */
	private $systemLogConfigurationBackup = '';

	/**
	 * wenn die nachricht nicht schon wieder geloggt werden soll
	 * leeren wir $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] um
	 * das zu verhindern.
	 *
	 * @param array $parameters
	 * @return void
	 *
	 * @todo refactoring to prevent the flooding in a more
	 * sophisticated way
	 */
	public function preventSystemLogFlood(array $parameters) {
		$this->handleSystemLogConfigurationBackup();

		/* @var $lockUtility tx_rnbase_util_Lock */
		$lockUtility = $this->getLockUtility($parameters);

		if ($lockUtility->isLocked()) {
			// prevent logging
			$GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] = '';
		} else {
			$lockUtility->lockProcess();
		}
	}

	/**
	 * @return void
	 */
	private function handleSystemLogConfigurationBackup() {
		// initial die systemLog Konfiguration sichern um diese ggf.
		// wieder zurück schreiben zu können
		if (!$this->systemLogConfigurationBackup) {
			$this->systemLogConfigurationBackup =
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'];
		} else {
			// wir schreiben die gesicherte Konfig zurück falls wir
			// vorher $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] geleert haben
			// um das logging zu verhindern
			$GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] =
				$this->systemLogConfigurationBackup;
		}
	}

	/**
	 * @param array $parameters
	 * @return tx_rnbase_util_Lock
	 */
	protected function getLockUtility(array $parameters) {
		return tx_rnbase_util_Lock::getInstance(
			md5(
				$parameters['msg'] . $parameters['extKey'] . $parameters['severity']
			),
			tx_mktools_util_miscTools::getSystemLogLockThreshold()
		);
	}
}