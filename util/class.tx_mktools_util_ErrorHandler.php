<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
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

tx_rnbase::load('Tx_Rnbase_Error_ErrorHandler');
tx_rnbase::load('Tx_Rnbase_Error_Exception');

/**
 * wie der TYPO3 error handler. aber wir behandeln noch fatal errors
 *
 * @author Hannes Bochmann
 * @package TYPO3
 * @subpackage tx_mktools
 */
class tx_mktools_util_ErrorHandler extends Tx_Rnbase_Error_ErrorHandler {

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
	 * @see Tx_Rnbase_Error_ErrorHandler::handleError()
	 *
	 * @throws tx_mktools_util_ErrorException
	 */
	public function handleError($errorLevel, $errorMessage, $errorFile, $errorLine) {
		if ($this->isErrorReportingDisabled()) {
			return TRUE;
		}
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
	 * @return boolean
	 */
	protected function isErrorReportingDisabled() {
		return error_reporting() === 0;
	}

	/**
	 * (non-PHPdoc)
	 * @see Tx_Rnbase_Error_ErrorHandler::handleError()
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
	 * @param Exception $exception
	 *
	 * @return void
	 */
	protected function writeExceptionToDevLog(Exception $exception) {
		$logTitle = 'Core: Error handler (' . TYPO3_MODE . ')';
		tx_rnbase::load('tx_rnbase_util_Logger');
		tx_rnbase_util_Logger::devLog($exception->getMessage(), $logTitle, 3);
	}

	/**
	 * @return boolean
	 */
	public function handleFatalError() {
		if ($this->isErrorReportingDisabled()) {
			return TRUE;
		}

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
			return TRUE;
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
		return new Tx_Rnbase_Error_Exception($exceptionMessage);
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
