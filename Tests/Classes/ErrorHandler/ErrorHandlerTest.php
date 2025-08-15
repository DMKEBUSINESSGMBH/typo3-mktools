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

namespace DMK\Mktools\Tests\ErrorHandler;

use DMK\Mktools\ErrorHandler\ErrorHandler;
use DMK\Mktools\ErrorHandler\ExceptionHandler;
use DMK\Mktools\Exception\ExceptionInterface;
use DMK\Mktools\Exception\RuntimeException;
use PHPUnit\Framework\Attributes\DataProvider;
use Sys25\RnBase\Testing\BaseTestCase;
use Sys25\RnBase\Typo3Wrapper\Core\Error\Exception as RnBaseException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ErrorHandlerTest.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class ErrorHandlerTest extends BaseTestCase
{
    /**
     * @var int
     */
    protected $originalErrorReporting;

    protected function tearDown(): void
    {
        if ($this->originalErrorReporting) {
            error_reporting($this->originalErrorReporting);
        }

        restore_exception_handler();
    }

    public function testHandleFatalErrorCallsNotExceptionHandlerIfErrorNotFatal(): void
    {
        $errorHandler = $this->getMock(
            ErrorHandler::class,
            ['getLastError', 'getExceptionHandler'],
            [[]]
        );

        $error = ['type' => E_WARNING];
        $errorHandler->expects($this->once())
            ->method('getLastError')
            ->willReturn($error);

        $errorHandler->expects($this->never())
            ->method('getExceptionHandler');

        $errorHandler->handleFatalError();
    }

    /**
     * @runInSeparateProcess
     */
    #[DataProvider('getErrorTypes')]
    public function testHandleFatalErrorCallsExceptionHandlerCorrectIfNotCatchableErrors(
        int $errorType,
        bool $errorHandled,
        bool $disableErrorRedprting = false,
    ): void {
        if ($disableErrorRedprting) {
            $this->disableErrorReporting();
        }

        $errorHandler = $this->getMock(
            ErrorHandler::class,
            ['getLastError', 'getExceptionHandler', 'getTypo3Exception'],
            [[]],
            '',
            false
        );

        if (!$disableErrorRedprting) {
            $error = ['type' => $errorType, 'message' => 'my error', 'line' => 123, 'file' => '123.php'];
            $errorHandler->expects($this->once())
                ->method('getLastError')
                ->willReturn($error);
        }

        $expectedErrorMessage = 'PHP Fatal Error: my error in '.basename('123.php').' line 123';
        $expectedException = new RnBaseException($expectedErrorMessage);
        $exceptionHandler = $this->getMock(
            ExceptionHandler::class,
            ['handleException']
        );
        if ($errorHandled) {
            $exceptionHandler->expects($this->once())
                ->method('handleException')
                ->with($expectedException);
            $errorHandler->expects($this->once())
                ->method('getExceptionHandler')
                ->willReturn($exceptionHandler);

            $errorHandler->expects($this->once())
                ->method('getTypo3Exception')
                ->with($expectedErrorMessage)
                ->willReturn($expectedException);
        } else {
            $errorHandler->expects($this->never())
                ->method('getExceptionHandler')
                ->willReturn($exceptionHandler);
        }

        $errorHandler->handleFatalError();
    }

    protected function disableErrorReporting()
    {
        $this->originalErrorReporting = error_reporting();
        error_reporting(0);
    }

    public static function getErrorTypes(): array
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

    public function testGetTypo3ExceptionReturnsCorrectExceptionType(): void
    {
        $handler = GeneralUtility::makeInstance(ErrorHandler::class, null);
        $method = new \ReflectionMethod(ErrorHandler::class, 'getTypo3Exception');

        $message = 'test';

        $exception = $method->invoke($handler, $message);
        $this->assertInstanceOf(
            \Exception::class,
            $exception,
            'Exception nicht vom Typ '
        );
        $this->assertEquals($message, $exception->getMessage(), 'Exception Nachricht falsch');
    }

    public function testHandleErrorLogsExceptionsIfShouldBeWrittenToDevLogAndThrowsMktoolsErrorException(): void
    {
        $errorHandler = $this->getMock(
            ErrorHandler::class,
            ['handleErrorByParent', 'shouldExceptionsBeWrittenToDevLog', 'writeExceptionToDevLog'],
            [1]
        );

        $exception = new RnBaseException('test');
        $errorHandler->expects($this->once())
            ->method('handleErrorByParent')
            ->with(1, 2, 3, 4)
            ->will($this->throwException($exception));

        $errorHandler->expects($this->once())
            ->method('shouldExceptionsBeWrittenToDevLog')
            ->willReturn(true);

        $errorHandler->expects($this->once())
            ->method('writeExceptionToDevLog')
            ->with($exception);

        try {
            $errorHandler->handleError(1, 2, 3, 4);
        } catch (ExceptionInterface $e) {
            $this->assertInstanceOf(
                RuntimeException::class,
                $e,
                'Exception nicht durchgereicht'
            );
        }
    }

    public function testHandleErrorLogsExceptionsNotIfShouldNotBeWrittenToDevLog(): void
    {
        $errorHandler = $this->getMock(
            ErrorHandler::class,
            ['handleErrorByParent', 'shouldExceptionsBeWrittenToDevLog', 'writeExceptionToDevLog'],
            [1]
        );

        $exception = new RnBaseException('test');
        $errorHandler->expects($this->once())
            ->method('handleErrorByParent')
            ->with(1, 2, 3, 4)
            ->will($this->throwException($exception));

        $errorHandler->expects($this->once())
            ->method('shouldExceptionsBeWrittenToDevLog')
            ->willReturn(false);

        $errorHandler->expects($this->never())
            ->method('writeExceptionToDevLog');

        try {
            $errorHandler->handleError(1, 2, 3, 4);
        } catch (ExceptionInterface $e) {
            $this->assertInstanceOf(
                RuntimeException::class,
                $e,
                'Exception nicht durchgereicht'
            );
        }
    }

    public function testHandleErrorLogsExceptionsNotIfNoExceptionThrown(): void
    {
        $errorHandler = $this->getMock(
            ErrorHandler::class,
            ['handleErrorByParent', 'shouldExceptionsBeWrittenToDevLog', 'writeExceptionToDevLog'],
            [1]
        );

        new RnBaseException('test');
        $errorHandler->expects($this->once())
            ->method('handleErrorByParent')
            ->with(1, 2, 3, 4)
            ->willReturn('test');

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

    public function testHandleErrorDoesNothingIfDisabledErrorReporting(): void
    {
        $this->disableErrorReporting();

        $errorHandler = $this->getMock(
            ErrorHandler::class,
            ['handleErrorByParent', 'shouldExceptionsBeWrittenToDevLog', 'writeExceptionToDevLog'],
            [1]
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
