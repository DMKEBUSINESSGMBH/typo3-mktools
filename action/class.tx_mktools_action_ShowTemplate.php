<?php
/**
 * 	@package TYPO3
 *  @subpackage tx_mktools
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2011 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
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
require_once t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_action_BaseIOC');

/**
 * Controller für Praxis-,Stellen-,Kooperations- und Vertreterangebote/-gesuche
 *
 * @package TYPO3
 * @subpackage tx_mktools
 */
class tx_mktools_action_ShowTemplate extends tx_rnbase_action_BaseIOC {

	/**
	 * Kindklassen führen hier die eigentliche Arbeit durch. Zugriff auf das
	 * Backend und befüllen der viewdata
	 *
	 * @param tx_rnbase_IParameters &$parameters
	 * @param tx_rnbase_configurations &$configurations
	 * @param ArrayObject &$viewdata
	 *
	 * @return string Errorstring or NULL
	 */
	protected function handleRequest(&$parameters, &$configurations, &$viewdata)
	{
		$viewdata->offsetSet('item', $this->getItem());

		return null;
	}

	/**
	 * Returns the data to render for the view
	 *
	 * @return array
	 */
	protected function getItem()
	{
		$data = $this->getData();

		return tx_rnbase::makeInstance(
			'tx_rnbase_model_base',
			is_array($data) ? $data : array()
		);
	}

	/**
	 * Returns the data to render for the view
	 *
	 * @return array
	 */
	protected function getData()
	{
		return array();
	}

	/**
	 * Gibt den Name des zugehörigen Templates zurück
	 *
	 * @return string
	 */
	public function getTemplateName()
	{
		return 'showtemplate';
	}

	/**
	 * Gibt den Name der zugehörigen View-Klasse zurück
	 *
	 * @return string
	 */
	public function getViewClassName()
	{
		return 'tx_mktools_view_ShowTemplate';
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mktools/action/class.tx_mktools_action_ShowTemplate.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mktools/action/class.tx_mktools_action_ShowTemplate.php']);
}
