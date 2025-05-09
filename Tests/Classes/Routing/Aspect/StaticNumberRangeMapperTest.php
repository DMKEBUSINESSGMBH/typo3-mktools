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

namespace DMK\Mktools\Tests\Routing\Aspect;

use DMK\Mktools\Routing\Aspect\StaticNumberRangeMapper;
use PHPUnit\Framework\Attributes\DataProvider;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class StaticNumberRangeMapperTest.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class StaticNumberRangeMapperTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    #[DataProvider('dataProviderConstrcutor')]
    public function testConstructorValidatesSettings(array $settings, string $exceptionMessage): void
    {
        if ('' !== $exceptionMessage && '0' !== $exceptionMessage) {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage($exceptionMessage);
        }

        $mapper = $this->getAccessibleMock(StaticNumberRangeMapper::class, ['generate'], [$settings]);
        self::assertSame($settings['start'], $mapper->_get('start'));
        self::assertSame($settings['end'], $mapper->_get('end'));
    }

    public static function dataProviderConstrcutor(): array
    {
        return [
            [[], 'start must be a integer'],
            [['start' => 'abc'], 'start must be a integer'],
            [['start' => 1], 'end must be a integer'],
            [['start' => 1, 'end' => 'abc'], 'end must be a integer'],
            [['start' => 1, 'end' => 1], 'end must be greater than start'],
            [['start' => 1, 'end' => 0], 'end must be greater than start'],
            [['start' => 0, 'end' => 1001], 'the range can not be greater than 1000'],
            [['start' => 0, 'end' => 1000], ''],
            [['start' => 0, 'end' => 2], ''],
        ];
    }

    #[DataProvider('dataProviderGenerate')]
    public function testGenerate(string $value, ?string $expectedResult): void
    {
        $mapper = new StaticNumberRangeMapper(['start' => 0, 'end' => 100]);
        self::assertSame($expectedResult, $mapper->generate($value));
    }

    #[DataProvider('dataProviderGenerate')]
    public function testResolve(string $value, ?string $expectedResult): void
    {
        $mapper = new StaticNumberRangeMapper(['start' => 0, 'end' => 100]);
        self::assertSame($expectedResult, $mapper->resolve($value));
    }

    public static function dataProviderGenerate(): array
    {
        return [
            ['0', '0'],
            ['1', '1'],
            ['2', '2'],
            ['100', '100'],
            ['101', null],
            ['100abc', null],
            ['abc', null],
            ['-1', null],
        ];
    }
}
