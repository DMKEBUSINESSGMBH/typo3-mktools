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
tx_rnbase::load('tx_mktools_util_ErrorHandler');
tx_rnbase::load('tx_mktools_util_ExceptionHandler');
tx_rnbase::load('t3lib_error_Exception');

/**
 * @package TYPO3
 * @author Hannes Bochmann
 */
class tx_mktools_tests_util_ErrorHandler_testcase extends Tx_Phpunit_TestCase
{

	/**
	 * @group unit
	 */
	public function testHandleFatalErrorCallsNotExceptionHandlerIfErrorNotFatal() {
		$errorHandler = $this->getMock(
			'tx_mktools_util_ErrorHandler', array('getLastError','getExceptionHandler'), array(array())
		);

		$error = array('type' => E_WARNING);
		$errorHandler->expects($this->once())
			->method('getLastError')
			->will($this->returnValue($error));

		$errorHandler->expects($this->never())
			->method('getExceptionHandler');

		$errorHandler->handleFatalError();
	}

	/**
	 * @group unit
	 * @dataProvider getErrorTypes
	 */
	public function testHandleFatalErrorCallsExceptionHandlerCorrectIfNotCatchableErrors(
		$errorType, $errorHandled
	) {
		$errorHandler = $this->getMock(
			'tx_mktools_util_ErrorHandler',
			array('getLastError','getExceptionHandler','getTypo3Exception'),
			array(array()), '', FALSE
		);

		$error = array('type' => $errorType, 'message' => 'my error', 'line' => 123, 'file' => '123.php');
		$errorHandler->expects($this->once())
			->method('getLastError')
			->will($this->returnValue($error));

		$expectedErrorMessage = 'PHP Fatal Error: my error in ' . basename('123.php') . ' line 123';
		$expectedException = new t3lib_error_Exception($expectedErrorMessage);
		$exceptionHandler = $this->getMock(
			'tx_mktools_util_ExceptionHandler', array('handleException')
		);
		if($errorHandled) {
			$exceptionHandler->expects($this->once())
				->method('handleException')
				->with($expectedException);
			$errorHandler->expects($this->once())
				->method('getExceptionHandler')
				->will($this->returnValue($exceptionHandler));
			
			$errorHandler->expects($this->once())
				->method('getTypo3Exception')
				->with($expectedErrorMessage)
				->will($this->returnValue($expectedException));
		} else {
			$errorHandler->expects($this->never())
				->method('getExceptionHandler')
				->will($this->returnValue($exceptionHandler));
		}

		$errorHandler->handleFatalError();
	}
	
	/**
	 * @return array
	 */
	public function getErrorTypes() {
		return array(
			array(E_ERROR, true),
			array(E_COMPILE_ERROR, true),
			array(E_CORE_ERROR, true),
			array(E_USER_ERROR, true),
			array(E_WARNING, FALSE)
		);
	}

	/**
	 * @group unit
	 */
	public function testGetTypo3ExceptionReturnsCorrectExceptionType() {
		$handler = tx_rnbase::makeInstance('tx_mktools_util_ErrorHandler',NULL);
		$method = new ReflectionMethod('tx_mktools_util_ErrorHandler', 'getTypo3Exception');
		$method->setAccessible(true);
		$message = 'test';

		$exception = $method->invoke($handler, $message);
		$this->assertInstanceOf(
			't3lib_error_Exception',
			$exception, 'Exception nicht vom Typ '
		);
		$this->assertEquals($message, $exception->getMessage(), 'Exception Nachricht falsch');
	}
	
	/**
	 * @group unit
	 */
	public function testHandleErrorLogsExceptionsIfShouldBeWrittenToDevLogAndThrowsMktoolsErrorException() {
		$errorHandler = $this->getMock(
			'tx_mktools_util_ErrorHandler', 
			array('handleErrorByParent', 'shouldExceptionsBeWrittenToDevLog','writeExceptionToDevLog'),
			array(1)
		);
		
		$exception = new t3lib_error_Exception('test');
		$errorHandler->expects($this->once())
			->method('handleErrorByParent')
			->with(1,2,3,4)
			->will($this->throwException($exception));
			
		$errorHandler->expects($this->once())
			->method('shouldExceptionsBeWrittenToDevLog')
			->will($this->returnValue(true));
		
		$errorHandler->expects($this->once())
			->method('writeExceptionToDevLog')
			->with($exception);
		
		try {
			$errorHandler->handleError(1,2,3,4);				
		} catch (tx_mktools_util_ErrorException $e) {
			$this->assertInstanceOf(
				'tx_mktools_util_ErrorException', $e, 'Exception nicht durchgereicht'
			);
		}
	}
	
	/**
	 * @group unit
	 */
	public function testHandleErrorLogsExceptionsNotIfShouldNotBeWrittenToDevLog() {
		$errorHandler = $this->getMock(
			'tx_mktools_util_ErrorHandler', 
			array('handleErrorByParent', 'shouldExceptionsBeWrittenToDevLog','writeExceptionToDevLog'),
			array(1)
		);
		
		$exception = new t3lib_error_Exception('test');
		$errorHandler->expects($this->once())
			->method('handleErrorByParent')
			->with(1,2,3,4)
			->will($this->throwException($exception));
			
		$errorHandler->expects($this->once())
			->method('shouldExceptionsBeWrittenToDevLog')
			->will($this->returnValue(FALSE));
		
		$errorHandler->expects($this->never())
			->method('writeExceptionToDevLog');
		
		try {
			$errorHandler->handleError(1,2,3,4);				
		} catch (tx_mktools_util_ErrorException $e) {
			$this->assertInstanceOf(
				'tx_mktools_util_ErrorException', $e, 'Exception nicht durchgereicht'
			);
		}
	}
	
	/**
	 * @group unit
	 */
	public function testHandleErrorLogsExceptionsNotIfNoExceptionThrown() {
		$errorHandler = $this->getMock(
			'tx_mktools_util_ErrorHandler', 
			array('handleErrorByParent', 'shouldExceptionsBeWrittenToDevLog','writeExceptionToDevLog'),
			array(1)
		);
		
		$exception = new t3lib_error_Exception('test');
		$errorHandler->expects($this->once())
			->method('handleErrorByParent')
			->with(1,2,3,4)
			->will($this->returnValue('test'));
			
		$errorHandler->expects($this->never())
			->method('shouldExceptionsBeWrittenToDevLog');
		
		$errorHandler->expects($this->never())
			->method('writeExceptionToDevLog');
		
		$this->assertEquals(
			'test', $errorHandler->handleError(1,2,3,4), 'falscher return value'
		);
	}
}