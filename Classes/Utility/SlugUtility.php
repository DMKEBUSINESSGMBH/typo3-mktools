<?php

namespace DMK\Mktools\Utility;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\DataHandling\Model\RecordStateFactory;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/***************************************************************
 *  Copyright notice
 *
 * (c) DMK E-BUSINESS GmbH <dev@dmk-ebusiness.com>
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
 * Class SlugUtility.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
final class SlugUtility
{
    public static function populateEmptySlugsInTable(string $table, string $field): void
    {
        /* @var $connection \TYPO3\CMS\Core\Database\Connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
        /* @var $queryBuilder \TYPO3\CMS\Core\Database\Query\QueryBuilder */
        $queryBuilder = $connection->createQueryBuilder();
        /* @var $querBuilder \TYPO3\CMS\Core\Database\Query\QueryBuilder */
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $statement = $queryBuilder
            ->select('*')
            ->from($table)
            ->where(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq($field, $queryBuilder->createNamedParameter('')),
                    $queryBuilder->expr()->isNull($field)
                )
            )
            ->addOrderBy('uid', 'asc')
            ->execute();

        $suggestedSlugs = [];

        $fieldConfig = $GLOBALS['TCA'][$table]['columns'][$field]['config'];
        $evalInfo = !empty($fieldConfig['eval']) ? GeneralUtility::trimExplode(',', $fieldConfig['eval'], true) : [];
        $hasToBeUniqueInSite = in_array('uniqueInSite', $evalInfo, true);
        $hasToBeUniqueInPid = in_array('uniqueInPid', $evalInfo, true);
        $slugHelper = GeneralUtility::makeInstance(SlugHelper::class, $table, $field, $fieldConfig);

        while ($record = $statement->fetch()) {
            $recordId = (int) $record['uid'];
            $pid = (int) $record['pid'];
            $languageId = (int) $record['sys_language_uid'];
            $pageIdInDefaultLanguage = $languageId > 0 ? (int) $record['l10n_parent'] : $recordId;
            $slug = $suggestedSlugs[$pageIdInDefaultLanguage][$languageId] ?? '';

            if (empty($slug)) {
                $slug = $slugHelper->generate($record, $pid);
            }

            $state = RecordStateFactory::forName($table)
                ->fromArray($record, $pid, $recordId);
            if ($hasToBeUniqueInSite && !$slugHelper->isUniqueInSite($slug, $state)) {
                $slug = $slugHelper->buildSlugForUniqueInSite($slug, $state);
            }
            if ($hasToBeUniqueInPid && !$slugHelper->isUniqueInPid($slug, $state)) {
                $slug = $slugHelper->buildSlugForUniqueInPid($slug, $state);
            }

            $connection->update(
                $table,
                [$field => $slug],
                ['uid' => $recordId]
            );
        }
    }
}
