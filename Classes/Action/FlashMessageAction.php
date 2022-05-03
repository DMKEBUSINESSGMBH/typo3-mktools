<?php

namespace DMK\Mktools\Action;

/***************************************************************
 *  Copyright notice
 *
 * (c) 2021 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

use DMK\Mktools\Session\FlashMessageStorage;
use Sys25\RnBase\Frontend\Controller\AbstractAction;
use Sys25\RnBase\Frontend\Request\RequestInterface;
use Sys25\RnBase\Frontend\View\Marker\ListView;

/**
 * ShowTemplate Controller.
 *
 * @author Michael Wagner
 */
class FlashMessageAction extends AbstractAction
{
    /**
     * @param RequestInterface $request
     *
     * @return null
     *
     * @throws \Sys25\RnBase\Exception\SkipActionException
     * @throws \tx_rnbase_exception_Skip
     */
    protected function handleRequest(RequestInterface $request)
    {
        // convert to user int. dont cache this output!
        $request->getConfigurations()->convertToUserInt();

        $request->getViewContext()->offsetSet(
            \Sys25\RnBase\Frontend\View\Marker\ListView::VIEWDATA_ITEMS,
            FlashMessageStorage::getInstance()->getMessages()
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
        return ListView::class;
    }
}
