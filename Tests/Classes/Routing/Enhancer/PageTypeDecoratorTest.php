<?php

declare(strict_types=1);

/*
 * Copyright notice
 *
 * (c) DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This file is part of the "mktools" Extension for TYPO3 CMS.
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GNU Lesser General Public License can be found at
 * www.gnu.org/licenses/lgpl.html
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

namespace DMK\Mktools\Tests\Routing\Enhancer;

use DMK\Mktools\Routing\Enhancer\PageTypeDecorator;
use PHPUnit\Framework\Attributes\DataProvider;
use TYPO3\CMS\Core\Routing\Route;
use TYPO3\CMS\Core\Routing\RouteCollection;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class PageTypeDecoratorTest.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class PageTypeDecoratorTest extends UnitTestCase
{
    #[DataProvider('dataProviderDecorateForMatching')]
    public function testDecorateForMatching(string $typeInRoute, string $typeInQueryParameters, ?string $expectedType): void
    {
        $_GET['type'] = $typeInQueryParameters;
        $routeCollection = new RouteCollection();
        $route = new Route('');
        if ('dontSet' !== $typeInRoute) {
            $route->setOption('_decoratedParameters', ['type' => $typeInRoute]);
        }

        $routeCollection->add('first', $route);

        $pageTypeDecorator = new PageTypeDecorator(['map' => ['/' => 0]]);
        $pageTypeDecorator->decorateForMatching($routeCollection, '/first');

        self::assertSame($expectedType, $route->getOption('_decoratedParameters')['type'] ?? null);
    }

    public static function dataProviderDecorateForMatching(): array
    {
        return [
            ['123', '0', '123'],
            ['0', '0', '0'],
            ['dontSet', '0', null],
            ['dontSet', '123', '123'],
            ['0', '123', '123'],
            ['456', '123', '456'],
        ];
    }

    #[DataProvider('dataProviderDecorateForGeneration')]
    public function testDecorateForGeneration(string $path, string $expectedPath): void
    {
        $routeCollection = new RouteCollection();
        $route = new Route($path);
        $routeCollection->add('first', $route);

        $pageTypeDecorator = new PageTypeDecorator(['map' => ['/' => 0]]);
        $pageTypeDecorator->decorateForGeneration($routeCollection, []);

        self::assertSame($expectedPath, $route->getPath());
    }

    public static function dataProviderDecorateForGeneration(): array
    {
        return [
            ['/somepath', '/somepath'],
            ['/index.html', '/'],
            ['/index/', '/'],
            ['/index', '/'],
        ];
    }
}
