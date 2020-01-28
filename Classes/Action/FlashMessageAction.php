<?php

namespace DMK\Mktools\Action;

use Sys25\RnBase\Configuration\Processor;
use Sys25\RnBase\Frontend\Request\Parameters;

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

/**
 * ShowTemplate Controller.
 *
 * @author Michael Wagner
 */
class FlashMessageAction extends \tx_rnbase_action_BaseIOC
{
    /**
     * Do the Magic.
     *
     * @param Parameters    $parameters
     * @param Processor $configurations
     * @param \ArrayObject              $viewdata
     *
     * @return null
     */
    protected function handleRequest(&$parameters, &$configurations, &$viewdata)
    {
        // convert to user int. dont cache this output!
        $this->getConfigurations()->convertToUserInt();

        $this->getViewData()->offsetSet(
            \tx_rnbase_view_List::VIEWDATA_ITEMS,
            \tx_mktools_util_FlashMessage::getInstance()->getMessages()
        );

        return null;
    }

    /**
     * Template name and ConfId.
     *
     * @return string
     */
    protected function getTemplateName()
    {
        return 'flashmessages';
    }

    /**
     * The view class.
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
