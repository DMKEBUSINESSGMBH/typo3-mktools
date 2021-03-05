<?php

declare(strict_types=1);

namespace DMK\Mktools\ErrorHandler;

/**
 * Class tx_mktools_util_ExceptionHandler.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class ThrowableExceptionHandler extends ExceptionHandler
{
    /**
     * {@inheritdoc}
     *
     * @see \TYPO3\CMS\Core\Error\ProductionExceptionHandler::echoExceptionWeb()
     */
    public function writeLogEntries(\Throwable $exception, $context)
    {
        parent::writeLogEntriesEnvironment($exception, $context);
    }

    /**
     * {@inheritdoc}
     *
     * @see \TYPO3\CMS\Core\Error\ProductionExceptionHandler::echoExceptionWeb()
     */
    public function sendStatusHeaders(\Throwable $exception)
    {
        parent::sendStatusHeadersEnvironment($exception);
    }

    /**
     * {@inheritdoc}
     *
     * @see \TYPO3\CMS\Core\Error\ProductionExceptionHandler::echoExceptionWeb()
     */
    public function echoExceptionWeb(\Throwable $exception)
    {
        parent::echoExceptionInWebEnvironment($exception);
    }
}
