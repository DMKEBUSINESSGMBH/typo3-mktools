<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 DMK E-BUSINESS GmbH (dev@dmk-ebusiness.de)
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

/**
 * @author Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 */
class tx_mktools_model_Pages extends tx_rnbase_model_base
{
    /**
     * @var tx_mktools_model_FixedPostVarType | bool | null
     */
    private $fixedPostVarType = false;

    /**
     * (non-PHPdoc).
     *
     * @see tx_rnbase_model_base::getTableName()
     */
    public function getTableName()
    {
        return 'pages';
    }

    /**
     * @return tx_mktools_model_FixedPostVarType|bool|null
     */
    public function getFixedPostVarType()
    {
        if (false === $this->fixedPostVarType) {
            if ($this->record['tx_mktools_fixedpostvartype']) {
                /** @var tx_mktools_model_FixedPostVarType $type */
                $type =  tx_rnbase::makeInstance(
                    'tx_mktools_model_FixedPostVarType',
                    $this->record['tx_mktools_fixedpostvartype']
                );
                $this->fixedPostVarType = $type;
            } else {
                $this->fixedPostVarType = null;
            }
        }

        return $this->fixedPostVarType;
    }
}
