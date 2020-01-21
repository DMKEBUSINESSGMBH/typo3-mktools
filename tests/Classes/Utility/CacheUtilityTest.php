<?php

namespace DMK\Mktools\Utility;

/**
 *  Copyright notice.
 *
 *  (c) Hannes Bochmann <dev@dmk-ebusiness.de>
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

/**
 * DMK\Mktools\Utility$CacheUtilityTest.
 *
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class CacheUtilityTest extends \tx_rnbase_tests_BaseTestCase
{
    /**
     * @var array
     */
    private $cachingConfigurationBackup = [];

    /**
     * @var bool
     */
    private static $apcLoaded = false;

    /**
     * @var bool
     */
    private static $apcuLoaded = false;

    /**
     * @var bool
     */
    private static $apcEnabled = false;

    /**
     * @var array
     */
    private $defaultCacheHashCachingConfiguration = [
        'backend' => 'defaultBackend',
        'frontend' => 'defaultFrontend',
        'options' => [
            'compression' => true,
            'defaultLifetime' => 0,
        ],
        'groups' => ['pages'],
    ];

    /**
     * @var array
     */
    private $expectedApcCachingConfiguration = [
        'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\ApcBackend',
        'frontend' => 'defaultFrontend',
        'options' => [
            'defaultLifetime' => 0,
        ],
        'groups' => ['pages'],
    ];

    /**
     * @var array
     */
    private $expectedApcuCachingConfiguration = [
        'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\ApcuBackend',
        'frontend' => 'defaultFrontend',
        'options' => [
            'defaultLifetime' => 0,
        ],
        'groups' => ['pages'],
    ];

    /**
     * {@inheritdoc}
     *
     * @see \PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->cachingConfigurationBackup = $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_hash'] =
            $this->defaultCacheHashCachingConfiguration;
    }

    /**
     * {@inheritdoc}
     *
     * @see \PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'] = $this->cachingConfigurationBackup;
    }

    /**
     * @return bool
     */
    public static function isApcEnabled()
    {
        return self::$apcEnabled;
    }

    /**
     * @return bool
     */
    public static function isApcLoaded()
    {
        return self::$apcLoaded;
    }

    /**
     * @return bool
     */
    public static function isApcuLoaded()
    {
        return self::$apcuLoaded;
    }

    /**
     * @param bool  $apcLoaded
     * @param bool  $apcuLoaded
     * @param bool  $apcEnabled
     * @param array $expectedCachingConfiguration
     * @dataProvider dataProviderUseApcAsCacheBackend
     */
    public function testUseApcAsCacheBackend($apcLoaded, $apcuLoaded, $apcEnabled, $expectedCachingConfiguration)
    {
        self::$apcLoaded = $apcLoaded;
        self::$apcuLoaded = $apcuLoaded;
        self::$apcEnabled = $apcEnabled;

        CacheUtility::useApcAsCacheBackend();

        self::assertSame(
            $expectedCachingConfiguration,
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_hash']
        );
    }

    /**
     * @return boolean[][]|array[][]|string[][][]|boolean[][][][]|number[][][][]|string[][][][]
     */
    public function dataProviderUseApcAsCacheBackend()
    {
        return [
            [false, false, false, $this->defaultCacheHashCachingConfiguration],
            [true, false, false, $this->defaultCacheHashCachingConfiguration],
            [false, true, false, $this->defaultCacheHashCachingConfiguration],
            [false, false, true, $this->defaultCacheHashCachingConfiguration],
            [true, true, false, $this->defaultCacheHashCachingConfiguration],
            [true, false, true, $this->expectedApcCachingConfiguration],
            [false, true, true, $this->expectedApcuCachingConfiguration],
            [true, true, true, $this->expectedApcCachingConfiguration],
        ];
    }

    public function testGetApcCacheBackendClass()
    {
        self::$apcLoaded = false;
        self::assertSame('TYPO3\\CMS\\Core\\Cache\\Backend\\ApcuBackend', CacheUtility::getApcCacheBackendClass());

        self::$apcLoaded = true;
        self::assertSame('TYPO3\\CMS\\Core\\Cache\\Backend\\ApcBackend', CacheUtility::getApcCacheBackendClass());
    }

    /**
     * @param bool $apcLoaded
     * @param bool $apcuLoaded
     * @param bool $apcEnabled
     * @param bool $isApcUsed
     * @dataProvider dataProviderIsApcUsed
     */
    public function testIsApcUsed($apcLoaded, $apcuLoaded, $apcEnabled, $isApcUsed)
    {
        self::$apcLoaded = $apcLoaded;
        self::$apcuLoaded = $apcuLoaded;
        self::$apcEnabled = $apcEnabled;

        self::assertSame($isApcUsed, CacheUtility::isApcUsed());
    }

    /**
     * @return boolean[][]|array[][]|string[][][]|boolean[][][][]|number[][][][]|string[][][][]
     */
    public function dataProviderIsApcUsed()
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

    public function testSetCacheBackend()
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
    $extensionLoaded = false;

    if ('apc' == $extension) {
        $extensionLoaded = CacheUtilityTest::isApcLoaded();
    }

    if ('apcu' == $extension) {
        $extensionLoaded = CacheUtilityTest::isApcuLoaded();
    }

    return $extensionLoaded;
}

/**
 * @param string $configurationPath
 *
 * @return mixed
 */
function ini_get($configurationPath)
{
    $configurationValue = '';

    if ('apc.enabled' == $configurationPath) {
        $configurationValue = CacheUtilityTest::isApcEnabled();
    }

    return $configurationValue;
}

/**
 * @param string $name
 *
 * @return string
 */
function constant(string $name)
{
    $constantValue = '';

    if ('PHP_SAPI' == $name) {
        $constantValue = 'notCli';
    }

    return $constantValue;
}
