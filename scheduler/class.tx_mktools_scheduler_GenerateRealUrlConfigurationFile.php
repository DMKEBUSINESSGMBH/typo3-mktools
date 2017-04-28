<?php
/***************************************************************
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
 ***************************************************************/


tx_rnbase::load('tx_mklib_scheduler_Generic');
tx_rnbase::load('tx_mktools_util_RealUrl');

/**
 * @author Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 */
class tx_mktools_scheduler_GenerateRealUrlConfigurationFile extends tx_mklib_scheduler_Generic
{

    /**
     *
     * @param   array   $options
     * @return  string
     */
    protected function executeTask(array $options, array &$devLog)
    {
        $realUrlUtil = $this->getRealUrlUtil();

        if ($realUrlUtil->needsRealUrlConfigurationToBeGenerated()) {
            $pagesWithFixedPostVarType = $realUrlUtil->getPagesWithFixedPostVarType();
            $realUrlConfigurationFileGenerated =
                $realUrlUtil->generateSerializedRealUrlConfigurationFileByPages(
                    $pagesWithFixedPostVarType
                );
            $devLogMessage = $realUrlConfigurationFileGenerated ?
                'realUrl Konfigurationsdatei wurde neu erstellt.' :
                'realUrl Konfigurationsdatei musste neu erstellt werden, was nicht funktioniert hat. Entweder stimmt die Extension Konfiguration nicht oder es gab einen Fehler beim Schreiben der Datei.';
        } else {
            $devLogMessage = 'realUrl Konfigurationsdatei muss nicht erstellt werden.';
        }

        $devLog[tx_rnbase_util_Logger::LOGLEVEL_INFO] = array(
            'message' => $devLogMessage,
        );
    }

    /**
     * @return tx_mktools_util_RealUrl
     */
    protected function getRealUrlUtil()
    {
        return tx_rnbase::makeInstance('tx_mktools_util_RealUrl');
    }

    /**
     * (non-PHPdoc)
     * @see tx_mklib_scheduler_Generic::getExtKey()
     */
    protected function getExtKey()
    {
        return 'mktools';
    }
}
