<?php

declare(strict_types=1);

namespace DMK\Mktools\ErrorHandler;

/***************************************************************
 *  Copyright notice
 *
 * (c) 2021 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
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
