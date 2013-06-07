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
tx_rnbase::load('tx_mktools_util_ErrorHandler');
tx_rnbase::load('tx_mktools_util_ExceptionHandler');
tx_rnbase::load('t3lib_error_Exception');

/**
 * @package tx_mktools
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
	 */
	public function testHandleFatalErrorCallsExceptionHandlerCorrectIfFatalError() {
		$errorHandler = $this->getMock(
			'tx_mktools_util_ErrorHandler', array('getLastError','getExceptionHandler'), array(array()),
			'', false
		);
		
		$error = array('type' => E_ERROR, 'message' => 'my error', 'line' => 123, 'file' => '123.php');
		$errorHandler->expects($this->once())
			->method('getLastError')
			->will($this->returnValue($error));

		$expectedErrorMessage = 'PHP Fatal Error: my error in ' . basename('123.php') . 'line 123';
		$expectedException = new t3lib_error_Exception($expectedErrorMessage);
		$exceptionHandler = $this->getMock(
			'tx_mktools_util_ExceptionHandler', array('echoExceptionWeb')
		);
		$exceptionHandler->expects($this->once())
			->method('echoExceptionWeb')
			->with($expectedException);
		
		$errorHandler->expects($this->once())
			->method('getExceptionHandler')
			->will($this->returnValue($exceptionHandler));
			
		$errorHandler->handleFatalError();
	}

}