<?php

namespace DMK\Mktools\Utility;

use Doctrine\DBAL\Driver\Statement;
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
        self::generateSlugsInTable($table, $field);
    }

    public static function migrateRealurlAliasToSlug(string $table, string $field): void
    {
        self::generateSlugsInTable($table, $field, true);
    }

    private static function generateSlugsInTable(string $table, string $field, bool $mindRealurlAlias = false): void
    {
        $connection = self::getConnectionForTable($table);
        $getRecordsStatement = self::getRecordsWithoutSlugInTableStatement($table, $field);
        $recordsWithOutRealurlAlias = [];
        while ($record = $getRecordsStatement->fetchAssociative()) {
            $slug = '';
            if ($mindRealurlAlias && !($slug = self::getRealurlAliasByRecord($table, $field, $record))) {
                $recordsWithOutRealurlAlias[] = $record;
                continue;
            }
            $slug = $slug ?? self::generateSlug($table, $field, $record);
            $connection->update($table, [$field => $slug], ['uid' => (int) $record['uid']]);
        }

        // If there are records without alias generate the slug after all aliases have bee migrated to make sure
        // the slugs are unique. Otherwise it might happen that a later migrated alias is the same as a previous generated
        // slug.
        foreach ($recordsWithOutRealurlAlias as $record) {
            $connection->update(
                $table,
                [$field => self::generateSlug($table, $field, $record)],
                ['uid' => (int) $record['uid']]
            );
        }
    }

    private static function getRecordsWithoutSlugInTableStatement(string $table, string $field): Statement
    {
        /* @var $queryBuilder \TYPO3\CMS\Core\Database\Query\QueryBuilder */
        $queryBuilder = self::getConnectionForTable($table)->createQueryBuilder();
        /* @var $querBuilder \TYPO3\CMS\Core\Database\Query\QueryBuilder */
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return $queryBuilder
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
    }

    private static function getConnectionForTable(string $table): \TYPO3\CMS\Core\Database\Connection
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
    }

    private static function generateSlug(string $table, string $field, array $record): string
    {
        $fieldConfig = $GLOBALS['TCA'][$table]['columns'][$field]['config'];
        $evalInfo = !empty($fieldConfig['eval']) ? GeneralUtility::trimExplode(',', $fieldConfig['eval'], true) : [];
        $hasToBeUniqueInSite = in_array('uniqueInSite', $evalInfo, true);
        $hasToBeUniqueInPid = in_array('uniqueInPid', $evalInfo, true);
        $slugHelper = GeneralUtility::makeInstance(SlugHelper::class, $table, $field, $fieldConfig);

        $recordId = (int) $record['uid'];
        $pid = (int) $record['pid'];
        $slug = $slugHelper->generate($record, $pid);

        $state = RecordStateFactory::forName($table)->fromArray($record, $pid, $recordId);
        if ($hasToBeUniqueInSite && !$slugHelper->isUniqueInSite($slug, $state)) {
            $slug = $slugHelper->buildSlugForUniqueInSite($slug, $state);
        }
        if ($hasToBeUniqueInPid && !$slugHelper->isUniqueInPid($slug, $state)) {
            $slug = $slugHelper->buildSlugForUniqueInPid($slug, $state);
        }

        return $slug;
    }

    private static function getRealurlAliasByRecord(string $table, string $field, array $record): string
    {
        /* @var $queryBuilder \TYPO3\CMS\Core\Database\Query\QueryBuilder */
        $queryBuilder = self::getConnectionForTable($table)->createQueryBuilder();

        return (string) $queryBuilder
            ->select('value_alias')
            ->from('tx_realurl_uniqalias')
            ->where('tablename = :table AND value_id = :uid')
            ->setParameter('table', $table)
            ->setParameter('uid', (int) $record['uid'])
            ->setMaxResults(1)
            ->execute()
            ->fetchAssociative()['value_alias']
        ;
    }
}
