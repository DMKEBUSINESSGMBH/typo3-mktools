<?php

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

namespace DMK\Mktools\Utility;

use PHPUnit\Framework\Attributes\DataProvider;
use TYPO3\CMS\Core\Cache\Backend\ApcuBackend;

/**
 * DMK\Mktools\Utility$CacheUtilityTest.
 *
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class CacheUtilityTest extends \Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * @var array
     */
    private mixed $cachingConfigurationBackup = [];

    private static bool $apcLoaded = false;

    private static bool $apcuLoaded = false;

    private static bool $apcEnabled = false;

    private static array $defaultCacheHashCachingConfiguration = [
        'backend' => 'defaultBackend',
        'frontend' => 'defaultFrontend',
        'options' => [
            'compression' => true,
            'defaultLifetime' => 0,
        ],
        'groups' => ['pages'],
    ];

    private static array $expectedApcCachingConfiguration = [
        'backend' => ApcuBackend::class,
        'frontend' => 'defaultFrontend',
        'options' => [
            'defaultLifetime' => 0,
        ],
        'groups' => ['pages'],
    ];

    private static array $expectedApcuCachingConfiguration = [
        'backend' => ApcuBackend::class,
        'frontend' => 'defaultFrontend',
        'options' => [
            'defaultLifetime' => 0,
        ],
        'groups' => ['pages'],
    ];

    protected function setUp(): void
    {
        $this->cachingConfigurationBackup = $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['hash'] =
            self::$defaultCacheHashCachingConfiguration;
    }

    protected function tearDown(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'] = $this->cachingConfigurationBackup;
    }

    public static function isApcEnabled(): bool
    {
        return self::$apcEnabled;
    }

    public static function isApcLoaded(): bool
    {
        return self::$apcLoaded;
    }

    public static function isApcuLoaded(): bool
    {
        return self::$apcuLoaded;
    }

    #[DataProvider('dataProviderUseApcAsCacheBackend')]
    public function testUseApcAsCacheBackend(bool $apcLoaded, bool $apcuLoaded, bool $apcEnabled, array $expectedCachingConfiguration): void
    {
        self::$apcLoaded = $apcLoaded;
        self::$apcuLoaded = $apcuLoaded;
        self::$apcEnabled = $apcEnabled;

        CacheUtility::useApcAsCacheBackend();

        self::assertSame(
            $expectedCachingConfiguration,
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['hash']
        );
    }

    /**
     * @return bool[][]|array[][]|string[][][]|bool[][][][]|number[][][][]|string[][][][]
     */
    public static function dataProviderUseApcAsCacheBackend(): array
    {
        return [
            [false, false, false, self::$defaultCacheHashCachingConfiguration],
            [true, false, false, self::$defaultCacheHashCachingConfiguration],
            [false, true, false, self::$defaultCacheHashCachingConfiguration],
            [false, false, true, self::$defaultCacheHashCachingConfiguration],
            [true, true, false, self::$defaultCacheHashCachingConfiguration],
            [true, false, true, self::$expectedApcCachingConfiguration],
            [false, true, true, self::$expectedApcuCachingConfiguration],
            [true, true, true, self::$expectedApcCachingConfiguration],
        ];
    }

    public function testGetApcCacheBackendClass(): void
    {
        self::$apcLoaded = false;
        self::assertSame(ApcuBackend::class, CacheUtility::getApcCacheBackendClass());

        self::$apcLoaded = true;
        self::assertSame(ApcuBackend::class, CacheUtility::getApcCacheBackendClass());
    }

    #[DataProvider('dataProviderIsApcUsed')]
    public function testIsApcUsed(bool $apcLoaded, bool $apcuLoaded, bool $apcEnabled, bool $isApcUsed): void
    {
        self::$apcLoaded = $apcLoaded;
        self::$apcuLoaded = $apcuLoaded;
        self::$apcEnabled = $apcEnabled;

        self::assertSame($isApcUsed, CacheUtility::isApcUsed());
    }

    /**
     * @return bool[][]|array[][]|string[][][]|bool[][][][]|number[][][][]|string[][][][]
     */
    public static function dataProviderIsApcUsed(): array
    {
        return [
            [false, false, false, false],
            [true, false, false, false],
            [false, true, false, false],
            [false, false, true, false],
            [true, true, false, false],
            [true, true, true, true],
            [true, false, true, true],
            [false, true, true, true],
        ];
    }

    public function testSetCacheBackend(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['test']['options']['compression'] = true;
        CacheUtility::setCacheBackend('backendClass', 'test');

        self::assertSame(
            'backendClass',
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['test']['backend']
        );
        self::assertArrayNotHasKey(
            'compression',
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['test']['options']
        );
    }
}

/**
 * @param string $extension
 *
 * @return bool
 */
function extension_loaded($extension)
{
    if ('apc' == $extension) {
        return CacheUtilityTest::isApcLoaded();
    }

    if ('apcu' == $extension) {
        return CacheUtilityTest::isApcuLoaded();
    }

    return false;
}

/**
 * @param string $configurationPath
 */
function ini_get($configurationPath): bool|string
{
    if ('apc.enabled' == $configurationPath) {
        return CacheUtilityTest::isApcEnabled();
    }

    return '';
}

function constant(string $name): string
{
    if ('PHP_SAPI' === $name) {
        return 'notCli';
    }

    return '';
}
