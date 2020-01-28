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

use DMK\Mktools\ErrorHandler\ErrorHandler;
use Sys25\RnBase\Typo3Wrapper\Core\Error\Exception as RnBaseException;

/**
 * @author Hannes Bochmann
 */
class tx_mktools_tests_util_ErrorHandlerTest extends tx_rnbase_tests_BaseTestCase
{
    /**
     * @var int
     */
    protected $originalErrorReporting;

    /**
     * {@inheritdoc}
     *
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        if ($this->originalErrorReporting) {
            error_reporting($this->originalErrorReporting);
        }
    }

    /**
     * @group unit
     */
    public function testHandleFatalErrorCallsNotExceptionHandlerIfErrorNotFatal()
    {
        $errorHandler = $this->getMock(
            ErrorHandler::class,
            array('getLastError', 'getExceptionHandler'),
            array(array())
        );

        $error = ['type' => E_WARNING];
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
        $errorType,
        $errorHandled,
        $disableErrorRedprting = false
    ) {
        if ($disableErrorRedprting) {
            $this->disableErrorReporting();
        }

        $errorHandler = $this->getMock(
            ErrorHandler::class,
            array('getLastError', 'getExceptionHandler', 'getTypo3Exception'),
            array(array()),
            '',
            false
        );

        if (!$disableErrorRedprting) {
            $error = ['type' => $errorType, 'message' => 'my error', 'line' => 123, 'file' => '123.php'];
            $errorHandler->expects($this->once())
                ->method('getLastError')
                ->will($this->returnValue($error));
        }

        $expectedErrorMessage = 'PHP Fatal Error: my error in '.basename('123.php').' line 123';
        $expectedException = new Tx_Rnbase_Error_Exception($expectedErrorMessage);
        $exceptionHandler = $this->getMock(
            'tx_mktools_util_ExceptionHandler',
            ['handleException']
        );
        if ($errorHandled) {
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

    protected function disableErrorReporting()
    {
        $this->originalErrorReporting = error_reporting();
        error_reporting(0);
    }

    /**
     * @return array
     */
    public function getErrorTypes()
    {
        return [
            [E_ERROR, true],
            [E_COMPILE_ERROR, true],
            [E_CORE_ERROR, true],
            [E_USER_ERROR, true],
            [E_WARNING, false],
            [E_ERROR, false, true],
            [E_COMPILE_ERROR, false, true],
            [E_CORE_ERROR, false, true],
            [E_USER_ERROR, false, true],
            [E_WARNING, false, true],
        ];
    }

    /**
     * @group unit
     */
    public function testGetTypo3ExceptionReturnsCorrectExceptionType()
    {
        $handler = tx_rnbase::makeInstance(ErrorHandler::class, null);
        $method = new ReflectionMethod(ErrorHandler::class, 'getTypo3Exception');
        $method->setAccessible(true);
        $message = 'test';

        $exception = $method->invoke($handler, $message);
        $this->assertInstanceOf(
            \Sys25\RnBase\Typo3Wrapper\Core\Error\Exception::class,
            $exception,
            'Exception nicht vom Typ '
        );
        $this->assertEquals($message, $exception->getMessage(), 'Exception Nachricht falsch');
    }

    /**
     * @group unit
     */
    public function testHandleErrorLogsExceptionsIfShouldBeWrittenToDevLogAndThrowsMktoolsErrorException()
    {
        $errorHandler = $this->getMock(
            ErrorHandler::class,
            array('handleErrorByParent', 'shouldExceptionsBeWrittenToDevLog', 'writeExceptionToDevLog'),
            array(1)
        );

        $exception = new RnBaseException('test');
        $errorHandler->expects($this->once())
            ->method('handleErrorByParent')
            ->with(1, 2, 3, 4)
            ->will($this->throwException($exception));

        $errorHandler->expects($this->once())
            ->method('shouldExceptionsBeWrittenToDevLog')
            ->will($this->returnValue(true));

        $errorHandler->expects($this->once())
            ->method('writeExceptionToDevLog')
            ->with($exception);

        try {
            $errorHandler->handleError(1, 2, 3, 4);
        } catch (\DMK\Mktools\Exception\ExceptionInterface $e) {
            $this->assertInstanceOf(
                \DMK\Mktools\Exception\RuntimeException::class,
                $e,
                'Exception nicht durchgereicht'
            );
        }
    }

    /**
     * @group unit
     */
    public function testHandleErrorLogsExceptionsNotIfShouldNotBeWrittenToDevLog()
    {
        $errorHandler = $this->getMock(
            ErrorHandler::class,
            array('handleErrorByParent', 'shouldExceptionsBeWrittenToDevLog', 'writeExceptionToDevLog'),
            array(1)
        );

        $exception = new RnBaseException('test');
        $errorHandler->expects($this->once())
            ->method('handleErrorByParent')
            ->with(1, 2, 3, 4)
            ->will($this->throwException($exception));

        $errorHandler->expects($this->once())
            ->method('shouldExceptionsBeWrittenToDevLog')
            ->will($this->returnValue(false));

        $errorHandler->expects($this->never())
            ->method('writeExceptionToDevLog');

        try {
            $errorHandler->handleError(1, 2, 3, 4);
        } catch (\DMK\Mktools\Exception\ExceptionInterface $e) {
            $this->assertInstanceOf(
                \DMK\Mktools\Exception\RuntimeException::class,
                $e,
                'Exception nicht durchgereicht'
            );
        }
    }

    /**
     * @group unit
     */
    public function testHandleErrorLogsExceptionsNotIfNoExceptionThrown()
    {
        $errorHandler = $this->getMock(
            ErrorHandler::class,
            array('handleErrorByParent', 'shouldExceptionsBeWrittenToDevLog', 'writeExceptionToDevLog'),
            array(1)
        );

        $exception = new Tx_Rnbase_Error_Exception('test');
        $errorHandler->expects($this->once())
            ->method('handleErrorByParent')
            ->with(1, 2, 3, 4)
            ->will($this->returnValue('test'));

        $errorHandler->expects($this->never())
            ->method('shouldExceptionsBeWrittenToDevLog');

        $errorHandler->expects($this->never())
            ->method('writeExceptionToDevLog');

        $this->assertEquals(
            'test',
            $errorHandler->handleError(1, 2, 3, 4),
            'falscher return value'
        );
    }

    /**
     * @group unit
     */
    public function testHandleErrorDoesNothingIfDisabledErrorReporting()
    {
        $this->disableErrorReporting();

        $errorHandler = $this->getMock(
            ErrorHandler::class,
            array('handleErrorByParent', 'shouldExceptionsBeWrittenToDevLog', 'writeExceptionToDevLog'),
            array(1)
        );

        $errorHandler->expects(self::never())
            ->method('handleErrorByParent');

        $errorHandler->expects(self::never())
            ->method('shouldExceptionsBeWrittenToDevLog');

        $errorHandler->expects(self::never())
            ->method('writeExceptionToDevLog');

        $errorHandler->handleError(1, 2, 3, 4);
    }
}
