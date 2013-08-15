<?php
/*  **********************************************************************  **
 *  Copyright notice
 *
 *  (c) 2012 das MedienKombinat GmbH <kontakt@das-medienkombinat.de>
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
tx_rnbase::load('tx_mktools_util_ExceptionHandler');
tx_rnbase::load('tx_mklib_tests_Util');

/**
 * @package tx_mktools
 * @author Hannes Bochmann
 */
class tx_mktools_tests_util_ExceptionHandler_testcase extends Tx_Phpunit_TestCase{

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp() {
		tx_mklib_tests_Util::disableDevlog();
		tx_mklib_tests_Util::storeExtConf('mktools');
	}
	
	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	protected function tearDown() {
		tx_mklib_tests_Util::restoreExtConf('mktools');
	}
	
	/**
	 * @group unit
	 */
	public function testechoExceptionWebCallsSendStatusHeaderWithCorrectException() {
		//damit der redirect nicht ausgeführt wird
		tx_mklib_tests_Util::setExtConfVar('exceptionPage', '', 'mktools');
		
		$exceptionHandler = $this->getExceptionHandlerMock();
		
		$exception = new Exception('test exception');
		$exceptionHandler->expects($this->once())
			->method('sendStatusHeaders')
			->with($exception);
		
		$exceptionHandler->echoExceptionWeb($exception);
	}
	
	/**
	 * @group unit
	 */
	public function testechoExceptionWebCallsWriteLogEntriesCorrect() {
		//damit der redirect nicht ausgeführt wird
		tx_mklib_tests_Util::setExtConfVar('exceptionPage', '', 'mktools');
		
		$exceptionHandler = $this->getExceptionHandlerMock();
		
		$exception = new Exception('test exception');
		$exceptionHandler->expects($this->once())
			->method('writeLogEntries')
			->with($exception, 'WEB');
		
		$exceptionHandler->echoExceptionWeb($exception);
	}
	
	/**
	 * @group unit
	 */
	public function testEchoExceptionWebCallsLogNoExceptionPageDefinedIfNoDefined() {
		tx_mklib_tests_Util::setExtConfVar('exceptionPage', 'FILE:', 'mktools');
		
		$exceptionHandler = $this->getExceptionHandlerMock(array('logNoExceptionPageDefined'));
		
		$exceptionHandler->expects($this->once())
			->method('logNoExceptionPageDefined');
			
		$exceptionHandler->expects($this->never())
			->method('echoExceptionPageAndExit');
		
		$exception = new Exception('test exception');
		$exceptionHandler->echoExceptionWeb($exception);
	}
	
	/**
	 * @group unit
	 */
	public function testEchoExceptionWebCallsLogNoExceptionPageDefinedNotIfExceptionPageDefined() {
		tx_mklib_tests_Util::setExtConfVar('exceptionPage', 'FILE:index.php', 'mktools');
		
		$exceptionHandler = $this->getExceptionHandlerMock(array('logNoExceptionPageDefined'));
		
		$exceptionHandler->expects($this->never())
			->method('logNoExceptionPageDefined');
			
		$exceptionHandler->expects($this->once())
			->method('echoExceptionPageAndExit');
		
		$exception = new Exception('test exception');
		$exceptionHandler->echoExceptionWeb($exception);
	}
	
	/**
	 * @group unit
	 */
	public function testEchoExceptionWebCallsEchoExceptionPageAndExitWithCorrectLinkWhenFileIsDefinedAsExceptionPage() {
		tx_mklib_tests_Util::setExtConfVar('exceptionPage', 'FILE:index.php', 'mktools');
		
		$exceptionHandler = $this->getExceptionHandlerMock(array('logNoExceptionPageDefined'));
		
		$exceptionHandler->expects($this->once())
			->method('echoExceptionPageAndExit')
			->with(t3lib_div::locationHeaderUrl('index.php'));
		
		$exception = new Exception('test exception');
		$exceptionHandler->echoExceptionWeb($exception);
	}
	
	/**
	 * @group unit
	 */
	public function testEchoExceptionWebCallsEchoExceptionPageAndExitWithCorrectLinkWhenTypoScriptIsDefinedAsExceptionPage() {
		tx_mklib_tests_Util::setExtConfVar(
			'exceptionPage', 
			'TYPOSCRIPT:typo3conf/ext/mktools/tests/fixtures/typoscript/errorHandling.txt', 
			'mktools'
		);
		
		$exceptionHandler = $this->getExceptionHandlerMock(array('logNoExceptionPageDefined'));
		
		$exceptionHandler->expects($this->once())
			->method('echoExceptionPageAndExit')
			->with(t3lib_div::locationHeaderUrl('index.php'));
		
		$exception = new Exception('test exception');
		$exceptionHandler->echoExceptionWeb($exception);
	}
	
	/**
	 * @group unit
	 */
	public function testWriteLogEntriesCallsParentIfExceptionIsNoMktoolsErrorException() {
		$exceptionHandler = $this->getMock(
			'tx_mktools_util_ExceptionHandler', 
			array('writeLogEntriesByParent')
		);
		
		$exception = new Exception('test', $code, $previous);
		$context = 'egal';
		$exceptionHandler->expects($this->once())
			->method('writeLogEntriesByParent')
			->with($exception, $context);
			
		$executeTaskMethod = new ReflectionMethod(
			'tx_mktools_util_ExceptionHandler', 'writeLogEntries'
		);
		$executeTaskMethod->setAccessible(true);
		$executeTaskMethod->invoke($exceptionHandler, $exception, $context);
	}
	
	/**
	 * @group unit
	 */
	public function testWriteLogEntriesCallsParentNotIfExceptionIsMktoolsErrorException() {
		$exceptionHandler = $this->getMock(
			'tx_mktools_util_ExceptionHandler', 
			array('writeLogEntriesByParent')
		);
		
		$exceptionHandler->expects($this->never())
			->method('writeLogEntriesByParent');
			
		$executeTaskMethod = new ReflectionMethod(
			'tx_mktools_util_ExceptionHandler', 'writeLogEntries'
		);
		$executeTaskMethod->setAccessible(true);
		$exception = tx_rnbase::makeInstance(
			'tx_mktools_util_ErrorException', 'test'
		);
		$context = 'egal';
		$executeTaskMethod->invoke($exceptionHandler, $exception, $context);
	}
	
	/**
	 * @param unknown_type $methods
	 * 
	 * @return tx_mktools_util_ExceptionHandler
	 */
	private function getExceptionHandlerMock($methods = array()) {
		$exceptionHandler = $this->getMock(
			'tx_mktools_util_ExceptionHandler', 
			array_merge(
				$methods,
				array('shouldExceptionBeDebugged', 'writeLogEntries', 'sendStatusHeaders', 'echoExceptionPageAndExit')
			)
		);
		
		$exceptionHandler->expects($this->once())
			->method('shouldExceptionBeDebugged')
			->will($this->returnValue(false));
			
		return $exceptionHandler;
	}
}