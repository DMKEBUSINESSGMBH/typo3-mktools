<?php

namespace DMK\Mktools\View;

use Sys25\RnBase\Frontend\Request\RequestInterface;
use Sys25\RnBase\Frontend\View\Marker\ListView;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

/**
 * Default view class to show a template.
 */
class ShowTemplate extends ListView
{
    public function createOutput($template, RequestInterface $request, $formatter)
    {
        $confId = $request->getConfId();
        $item = $request->getViewContext()->offsetGet('item');
        $itemPath = $this->getItemPath($request->getConfigurations(), $confId);
        $markerClass = $this->getMarkerClass($request->getConfigurations(), $confId);

        $marker = GeneralUtility::makeInstance($markerClass);

        $out = $marker->parseTemplate($template, $item, $formatter, $confId.$itemPath.'.', strtoupper($itemPath));

        return $out;
    }
}
