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

namespace DMK\Mktools\Routing\Aspect;

use TYPO3\CMS\Core\Routing\Aspect\StaticMappableAspectInterface;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Class StaticNumberRangeMapper.
 *
 * Same as StaticRangeMapper but accepts only numbers and doesn't implement the countable interface so this mapper
 * isn't considered in the PageRouter when calculating the product of all configured RangeMappers.
 *
 * Example:
 *   routeEnhancers:
 *     MyBlogPlugin:
 *       type: Extbase
 *       extension: BlogExample
 *       plugin: Pi1
 *       routes:
 *         - { routePath: '/list/{paging_widget}', _controller: 'BlogExample::list', _arguments: {'paging_widget': '@widget_0/currentPage'}}
 *         - { routePath: '/glossary/{section}', _controller: 'BlogExample::glossary'}
 *       defaultController: 'BlogExample::list'
 *       requirements:
 *         paging_widget: '\d+'
 *       aspects:
 *         paging_widget:
 *           type: StaticNumberRangeMapper
 *           start: '1'
 *           end: '1000'
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class StaticNumberRangeMapper implements StaticMappableAspectInterface
{
    /**
     * @var int
     */
    public const MAX_PAGE_LIMIT = 1000;

    /**
     * @var int
     */
    protected $start;

    /**
     * @var int
     */
    protected $end;

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(array $settings)
    {
        $start = $settings['start'] ?? null;
        $end = $settings['end'] ?? null;

        if (!MathUtility::canBeInterpretedAsInteger($start)) {
            throw new \InvalidArgumentException('start must be a integer', 1538277163);
        }
        if (!MathUtility::canBeInterpretedAsInteger($end)) {
            throw new \InvalidArgumentException('end must be a integer', 1538277164);
        }
        if (!($end > $start)) {
            throw new \InvalidArgumentException('end must be greater than start', 1538277164);
        }
        if (($end - $start) > self::MAX_PAGE_LIMIT) {
            throw new \InvalidArgumentException('the range can not be greater than '.self::MAX_PAGE_LIMIT, 1538277164);
        }

        $this->start = intval($start);
        $this->end = intval($end);
    }

    /**
     * {@inheritdoc}
     */
    public function generate(string $value): ?string
    {
        return $this->pageIsInRange($value) ? $value : null;
    }

    protected function pageIsInRange(string $page): bool
    {
        return MathUtility::canBeInterpretedAsInteger($page) && $page >= $this->start && $page <= $this->end;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $value): ?string
    {
        return $this->generate($value);
    }
}
