<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
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
 ***************************************************************/
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
require_once(PATH_t3lib . 'error/interface.t3lib_error_errorhandlerinterface.php');
require_once(PATH_t3lib . 'error/interface.t3lib_error_exceptionhandlerinterface.php');
require_once(PATH_t3lib . 'interfaces/interface.t3lib_singleton.php');
require_once(PATH_t3lib . 'error/class.t3lib_error_abstractexceptionhandler.php');
require_once(PATH_t3lib . 'error/class.t3lib_error_errorhandler.php');

/**
 * wie der TYPO3 error handler. aber wir behandeln noch fatal errors
 *
 * @author Hannes Bochmann
 * @package TYPO3
 * @subpackage tx_mktools
 */
class tx_mktools_util_ErrorHandler extends t3lib_error_ErrorHandler {

	/**
	 * registriert den error handler auch für fatal errors
	 * @param int $errorHandlerErrors
	 *
	 * @return void
	 */
	public function __construct($errorHandlerErrors) {
		parent::__construct($errorHandlerErrors);
		register_shutdown_function(array($this, "handleFatalError" ));
	}
	
	/**
	 * wir loggen immer alle, Fehler, die exceptional sind für folgenden Fall:
	 * wenn ein Error geworfen wird, der exceptional ist und der Error
	 * wird in einem try-catch-block geworfen, dann wird der fehler verschluckt
	 * da die exception, welche für den exception handler geworfen wird, 
	 * gefangen wird
	 * 
	 * (non-PHPdoc)
	 * @see t3lib_error_ErrorHandler::handleError()
	 * 
	 * @throws tx_mktools_util_ErrorException
	 */
	public function handleError($errorLevel, $errorMessage, $errorFile, $errorLine) {
		try {
			$return = $this->handleErrorByParent(
				$errorLevel, $errorMessage, $errorFile, $errorLine
			);
		} catch (Exception $exception) {
			if ($this->shouldExceptionsBeWrittenToDevLog()) {
				$this->writeExceptionToDevLog($exception);
			}
			
			//damit der ExceptionHandler nicht nochmal einen Logeintrag schreibt.
			//dieser tut das nur für exceptions != tx_mktools_util_ErrorException
			throw tx_rnbase::makeInstance(
				'tx_mktools_util_ErrorException', 
				$exception->getMessage(), $exception->getCode()
			);
		}
		
		return $return;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see t3lib_error_ErrorHandler::handleError()
	 */
	protected function handleErrorByParent($errorLevel, $errorMessage, $errorFile, $errorLine) {
		return parent::handleError($errorLevel, $errorMessage, $errorFile, $errorLine);
	}
	
	/**
	 * @return boolean
	 */
	protected function shouldExceptionsBeWrittenToDevLog()  {
		return TYPO3_EXCEPTION_DLOG;
	}
	
	/**
	 * @param t3lib_error_Exception $exception
	 * 
	 * @return void
	 */
	protected function writeExceptionToDevLog(t3lib_error_Exception $exception) {
		$logTitle = 'Core: Error handler (' . TYPO3_MODE . ')';
		t3lib_div::devLog($exception->getMessage(), $logTitle, 3);
	}

	/**
	 * @return boolean
	 */
	public function handleFatalError() {
		$error = $this->getLastError();

		if(
			$error['type'] == E_ERROR ||
			$error['type'] == E_COMPILE_ERROR ||
			$error['type'] == E_CORE_ERROR ||
			$error['type'] == E_USER_ERROR
		) {
			$errorMessage = $error['message'];
			$errorFile = $error['file'];
			$errorLine = $error['line'];
			$message = 	'PHP Fatal Error: ' . $errorMessage . ' in ' .
						basename($errorFile) . ' line ' . $errorLine;

			$exception = $this->getTypo3Exception($message);
			$this->getExceptionHandler()->handleException($exception);
			return true;
		}
	}

	/**
	 * wird in Tests gemocked
	 *
	 * @return array
	 */
	protected function getLastError() {
		return error_get_last();
	}

	/**
	 * wird in Tests gemocked
	 *
	 * @param string $exceptionMessage
	 *
	 * @return tx_mktools_util_ExceptionHandler
	 */
	protected function getTypo3Exception($exceptionMessage) {
		return new t3lib_error_Exception($exceptionMessage);
	}

	/**
	 * wird in Tests gemocked
	 *
	 * @return tx_mktools_util_ExceptionHandler
	 */
	protected function getExceptionHandler() {
		return tx_rnbase::makeInstance('tx_mktools_util_ExceptionHandler');
	}
}