<?php
/*  **********************************************************************  **
 *  Copyright notice
 *
 *  (c) 2012 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
 *  ***********************************************************************  */
require_once(t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php'));
tx_rnbase::load('Tx_Phpunit_TestCase');
tx_rnbase::load('tx_mktools_util_PageNotFoundHandling');

/**
 *
 * @package TYPO3
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mktools_tests_util_PageNotFoundHandling_testcase
	extends Tx_Phpunit_TestCase
{

	/**
	 * @var string
	 */
	private $defaultPageTsConfig;

	public function setUp() {
		$this->defaultPageTsConfig = $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultPageTSconfig'];

		self::getTsFe()->id = '';
		self::getTsFe()->pageNotFound = 0;
	}

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	protected function tearDown() {
		$GLOBALS['TYPO3_CONF_VARS']['BE']['defaultPageTSconfig'] = $this->defaultPageTsConfig;
	}

	/**
	 * @group unit
	 */
	public function testRegisterXclass() {
		try {
			tx_mktools_util_PageNotFoundHandling::registerXclass();
		} catch (LogicException $e) {
			if ($e->getCode() !== intval(ERROR_CODE_MKTOOLS  . '130')) {
				throw $e;
			}
			$this->markTestSkipped(
				'There is another allready registred xclass!'
			);
		}

		if (tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
			$xclass =\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
				'TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController',
				array(), 0, 0
			);
		} else {
			$property = new ReflectionProperty('t3lib_div', 'finalClassNameRegister');
			$property->setAccessible(true);
			$property->setValue(null, array());
			$xclass = t3lib_div::makeInstance('tslib_fe', array(), 0, 0);
		}

		$this->assertInstanceOf('ux_tslib_fe', $xclass, 'xclass falsch');
	}

	/**
	 * Wir rufen den Handler ohne Werte auf.
	 * Der Handler darf dann nix tun!
	 */
	public function testHandlePageNotFoundWithoutMkToolsConfig() {
		$util = self::getPageNotFoundHandlingUtil();
		$ret = $util->handlePageNotFound('');

		$this->assertNull($ret);
		$this->assertTrue(is_array($util->getTestValue()));
		$this->assertCount(0, $util->getTestValue());
	}
	/**
	 * Wir rufen den Handler READFILE.
	 * Der Handler sollte nichts tun, da ignorecodes zutrifft
	 * utilPageNotFoundHandlingPrintContent.html zurückgeben!
	 */
	public function testHandlePageNotFoundWithReadfileAndIgnoreCode() {
		self::getTsFe()->pageNotFound = 1; //ID was not an accessible page
		$util = self::getPageNotFoundHandlingUtil('Test html/utilPageNotFoundHandlingPrintContent.');
		$printContentFile  = 'EXT:mktools/tests/fixtures/html/';
		$printContentFile .= 'utilPageNotFoundHandlingPrintContent.html';
		$ret = $util->handlePageNotFound('MKTOOLS_READFILE:'.$printContentFile);
		$testData = $util->getTestValue();

		$this->assertNull($ret);
		$this->assertTrue(is_array($util->getTestValue()));
		$this->assertCount(0, $util->getTestValue());
	}
	/**
	 * Wir rufen den Handler READFILE.
	 * Der Handler sollte den inhalt von
	 * utilPageNotFoundHandlingPrintContent.html zurückgeben!
	 */
	public function testHandlePageNotFoundWithReadfile() {
		$reason = 'Test html/utilPageNotFoundHandlingPrintContent.';
		$util = self::getPageNotFoundHandlingUtil($reason);
		$printContentFile  = 'EXT:mktools/tests/fixtures/html/';
		$printContentFile .= 'utilPageNotFoundHandlingPrintContent.html';
		$ret = $util->handlePageNotFound('MKTOOLS_READFILE:'.$printContentFile);
		$testData = $util->getTestValue();

		$this->assertNull($ret);
		$this->assertTrue(is_array($testData));
		$this->assertCount(2, $testData);
		$this->assertArrayHasKey('contentOrUrl', $testData);
		$this->assertGreaterThan(0, strpos($testData['contentOrUrl'], $reason));
		$this->assertEquals('3066a93a2e6ffad044540668b83572f1', md5($testData['contentOrUrl']));
		$this->assertArrayHasKey('httpStatus', $testData);
		$this->assertEquals('HTTP/1.1 404 Not Found', $testData['httpStatus']);
	}
	/**
	 * Wir rufen den Handler REDIRECT.
	 * Der Handler sollte die Url zurückgeben!
	 */
	public function testHandlePageNotFoundWithRedirect() {
		$util = self::getPageNotFoundHandlingUtil();
		$url = 'http://www.dmk-ebusiness.de/';
		$ret = $util->handlePageNotFound('MKTOOLS_REDIRECT:'.$url);
		$testData = $util->getTestValue();

		$this->assertNull($ret);
		$this->assertTrue(is_array($testData));
		$this->assertCount(2, $testData);
		$this->assertArrayHasKey('contentOrUrl', $testData);
		$this->assertEquals($url, $testData['contentOrUrl']);
		$this->assertArrayHasKey('httpStatus', $testData);
		$this->assertEquals('HTTP/1.1 404 Not Found', $testData['httpStatus']);
	}

	/**
	 * Wir rufen den Handler TYPOSCRIPT.
	 * Der Handler sollte den inhalt von
	 * utilPageNotFoundHandlingPrintContent.html zurückgeben!
	 */
	public function testHandlePageNotFoundWithTyposcriptConfigForReadfile() {
		$reason = 'Test typoscript/utilPageNotFoundHandlingPrintContent.txt';
		$util = self::getPageNotFoundHandlingUtil($reason);
		$printContentFile  = 'EXT:mktools/tests/fixtures/typoscript/';
		$printContentFile .= 'utilPageNotFoundHandlingPrintContent.txt';
		$ret = $util->handlePageNotFound('MKTOOLS_TYPOSCRIPT:'.$printContentFile);
		$testData = $util->getTestValue();

		$this->assertNull($ret);
		$this->assertTrue(is_array($testData));
		$this->assertCount(2, $testData);
		$this->assertArrayHasKey('contentOrUrl', $testData);
		$this->assertGreaterThan(0, strpos($testData['contentOrUrl'], $reason));
		$this->assertEquals('8de486b9d88e5436dfd90c0a7f7e0037', md5($testData['contentOrUrl']));
		$this->assertArrayHasKey('httpStatus', $testData);
		$this->assertEquals('HTTP/1.1 403 Forbidden', $testData['httpStatus']);
	}

	/**
	 * Wir rufen den Handler TYPOSCRIPT.
	 * Der Handler sollte die Url zurückgeben!
	 */
	public function testHandlePageNotFoundWithTyposcriptConfigForRedirect() {
		$util = self::getPageNotFoundHandlingUtil();
		$printContentFile  = 'EXT:mktools/tests/fixtures/typoscript/';
		$printContentFile .= 'utilPageNotFoundHandlingRedirect.txt';
		$ret = $util->handlePageNotFound('MKTOOLS_TYPOSCRIPT:'.$printContentFile);
		$testData = $util->getTestValue();
		$url = t3lib_div::locationHeaderUrl('/404.html');

		$this->assertNull($ret);
		$this->assertTrue(is_array($testData));
		$this->assertCount(2, $testData);
		$this->assertArrayHasKey('contentOrUrl', $testData);
		$this->assertEquals($url, $testData['contentOrUrl']);
		$this->assertArrayHasKey('httpStatus', $testData);
		$this->assertEquals('HTTP/1.1 403 Forbidden', $testData['httpStatus']);
	}

	/**
	 * Wir rufen den Handler TYPOSCRIPT.
	 * Der Handler sollte den inhalt von
	 * utilPageNotFoundHandlingPrintContent.html zurückgeben!
	 */
	public function testHandlePageNotFoundWithTyposcriptConfigForCertainCode() {
		$reason = 'Test typoscript/utilPageNotFoundHandlingPrintContentForCode4.txt';
		self::getTsFe()->pageNotFound = 4;
		$util = self::getPageNotFoundHandlingUtil($reason);
		$printContentFile  = 'EXT:mktools/tests/fixtures/typoscript/';
		$printContentFile .= 'utilPageNotFoundHandlingPrintContent.txt';
		$ret = $util->handlePageNotFound('MKTOOLS_TYPOSCRIPT:'.$printContentFile);
		$testData = $util->getTestValue();

		$this->assertNull($ret);
		$this->assertTrue(is_array($testData));
		$this->assertCount(2, $testData);
		$this->assertArrayHasKey('contentOrUrl', $testData);
		$this->assertGreaterThan(0, strpos($testData['contentOrUrl'], $reason));
		$this->assertEquals('f107de71054dad59b21983e70b7c824a', md5($testData['contentOrUrl']));
		$this->assertArrayHasKey('httpStatus', $testData);
		$this->assertEquals('HTTP/1.1 403 Forbidden', $testData['httpStatus']);
	}

	/**
	 * @return tx_mktools_tests_fixtures_classes_util_PageNotFoundHandling
	 */
	private static function getPageNotFoundHandlingUtil($reason = '') {
		tx_rnbase::load('tx_mktools_tests_fixtures_classes_util_PageNotFoundHandling');
		$obj = tx_mktools_tests_fixtures_classes_util_PageNotFoundHandling::getInstance(
					self::getTsFe(), $reason
				);
		$obj->setTestMode(true);
		return $obj;
	}

	/**
	 * @return tslib_fe
	 */
	private static function getTsFe()
	{
		if (!is_object($GLOBALS['TSFE'])) {
			tx_rnbase::load('tx_rnbase_util_Misc');
			tx_rnbase_util_Misc::prepareTSFE();
			$GLOBALS['TSFE']->id = '';
			$GLOBALS['TSFE']->pageNotFound = 0;
		}
		return $GLOBALS['TSFE'];
	}



	/**
	 * Asserts the number of elements of an array, Countable or Iterator.
	 * Dies ist erst ab tx_phpunit 3.6.10 verfügbar!
	 *
	 * @param integer $expectedCount
	 * @param mixed   $haystack
	 * @param string  $message
	 */
	public static function assertCount($expectedCount, $haystack, $message = '')
	{
		if (method_exists(PHPUnit_Framework_Assert, 'assertCount')) {
			parent::assertCount($expectedCount, $haystack);
		}
		self::assertEquals($expectedCount, count($haystack), $message);
	}

}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']
		['ext/mktools/tests/util/class.tx_mktools_tests_util_PageNotFoundHandling_testcase.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']
		['ext/mktools/tests/util/class.tx_mktools_tests_util_PageNotFoundHandling_testcase.php']);
}
