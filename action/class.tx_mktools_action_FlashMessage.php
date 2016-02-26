<?php
/***************************************************************
 *  Copyright notice
 *
 * (c) 2016 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

tx_rnbase::load('tx_rnbase_action_BaseIOC');

/**
 * ShowTemplate Controller
 *
 * @package TYPO3
 * @subpackage tx_mktools
 * @author Michael Wagner
 */
class tx_mktools_action_FlashMessage
	extends tx_rnbase_action_BaseIOC
{

	/**
	 * Do the Magic
	 *
	 * @param tx_rnbase_IParameters $parameters
	 * @param tx_rnbase_configurations $configurations
	 * @param ArrayObject $viewdata
	 *
	 * @return string Errorstring or NULL
	 */
	protected function handleRequest(&$parameters, &$configurations, &$viewdata)
	{
		tx_rnbase::load('tx_mktools_util_FlashMessage');
		tx_rnbase::load('tx_rnbase_view_List');

		// convert to user int. dont cache this output!
		$this->getConfigurations()->convertToUserInt();

		$this->getViewData()->offsetSet(
			tx_rnbase_view_List::VIEWDATA_ITEMS,
			tx_mktools_util_FlashMessage::getInstance()->getMessages()
		);

		return NULL;
	}
	/**
	 * Template name and ConfId
	 *
	 * @return string
	 */
	protected function getTemplateName()
	{
		return 'flashmessages';
	}

	/**
	 * The view class
	 *
	 * @return string
	 */
	protected function getViewClassName()
	{
		return 'tx_rnbase_view_List';
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mktools/action/class.tx_mktools_action_FlashMessage.php']) {
	require_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mktools/action/class.tx_mktools_action_FlashMessage.php'];
}
