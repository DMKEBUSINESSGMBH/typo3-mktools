<?php

declare(strict_types=1);

/**
 *  Copyright notice.
 *
 *  (c) DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

namespace DMK\Mktools\Routing\Enhancer;

use TYPO3\CMS\Core\Routing\RouteCollection;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PageTypeDecorator.
 *
 * Contrary to the original PageTypeDecorator this class checks if there is a type parameter without mapping given
 * and uses this instead of no mapped type parameter is found..
 *
 * This
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class PageTypeDecorator extends \TYPO3\CMS\Core\Routing\Enhancer\PageTypeDecorator
{
    /**
     * @var array
     */
    public const IGNORE_INDEX = [
        '/index.html',
        '/index/',
        '/index',
    ];

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function decorateForMatching(RouteCollection $collection, string $routePath): void
    {
        parent::decorateForMatching($collection, $routePath);

        foreach ($collection->all() as $route) {
            $decoratedParameters = $route->getOption('_decoratedParameters');
            $overwriteType = (($decoratedParameters['type'] ?? 0) == 0) && GeneralUtility::_GP('type');
            if ($overwriteType) {
                $decoratedParameters['type'] = GeneralUtility::_GP('type');
                $route->setOption('_decoratedParameters', $decoratedParameters);
            }
        }
    }

    public function decorateForGeneration(RouteCollection $collection, array $parameters): void
    {
        parent::decorateForGeneration($collection, $parameters);

        foreach ($collection->all() as $route) {
            if (true === \in_array($route->getPath(), self::IGNORE_INDEX, true)) {
                $route->setPath('/');
            }
        }
    }
}
