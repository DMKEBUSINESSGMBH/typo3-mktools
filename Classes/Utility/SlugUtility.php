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
    /**
     * @var string
     */
    private $table;

    /**
     * @var string
     */
    private $field;

    public function __construct(string $table, string $field)
    {
        $this->table = $table;
        $this->field = $field;
    }

    public function populateEmptySlugsInTable(): void
    {
        $this->generateUniqueSlugsInTable();
    }

    public function migrateRealurlAliasToSlug(): void
    {
        $this->generateUniqueSlugsInTable(true);
    }

    private function generateUniqueSlugsInTable(bool $mindRealurlAlias = false): void
    {
        $connection = $this->getConnectionForTable($this->table);
        $getRecordsStatement = $this->getRecordsWithoutSlugInTableStatement();
        while ($record = $getRecordsStatement->fetchAssociative()) {
            $realurlAlias = '';
            if ($mindRealurlAlias) {
                $realurlAlias = $this->getRealurlAliasByRecord($record);
            }
            $slug = $this->generateUniqueSlug($record, $realurlAlias);
            $connection->update($this->table, [$this->field => $slug], ['uid' => (int) $record['uid']]);
        }
    }

    private function getRecordsWithoutSlugInTableStatement(): Statement
    {
        /* @var $queryBuilder \TYPO3\CMS\Core\Database\Query\QueryBuilder */
        $queryBuilder = $this->getConnectionForTable($this->table)->createQueryBuilder();
        /* @var $querBuilder \TYPO3\CMS\Core\Database\Query\QueryBuilder */
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return $queryBuilder
            ->select('*')
            ->from($this->table)
            ->where(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq($this->field, $queryBuilder->createNamedParameter('')),
                    $queryBuilder->expr()->isNull($this->field)
                )
            )
            ->addOrderBy('uid', 'asc')
            ->execute();
    }

    private function getConnectionForTable(string $table): \TYPO3\CMS\Core\Database\Connection
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
    }

    public function generateUniqueSlug(array $record, string $slug = ''): string
    {
        $fieldConfig = $GLOBALS['TCA'][$this->table]['columns'][$this->field]['config'];
        $evalInfo = !empty($fieldConfig['eval']) ? GeneralUtility::trimExplode(',', $fieldConfig['eval'], true) : [];
        $hasToBeUniqueInSite = in_array('uniqueInSite', $evalInfo, true);
        $hasToBeUniqueInPid = in_array('uniqueInPid', $evalInfo, true);
        /* @var $slugHelper SlugHelper */
        $slugHelper = GeneralUtility::makeInstance(SlugHelper::class, $this->table, $this->field, $fieldConfig);

        $recordId = (int) $record['uid'];
        $pid = (int) $record['pid'];
        $slug = $slug ?: $slugHelper->generate($record, $pid);

        $state = RecordStateFactory::forName($this->table)->fromArray($record, $pid, $recordId);
        $uniqueSlug = '';
        if ($hasToBeUniqueInSite && !$slugHelper->isUniqueInSite($slug, $state)) {
            $uniqueSlug = $slugHelper->buildSlugForUniqueInSite($slug, $state);
        }
        if (!$uniqueSlug && $hasToBeUniqueInPid && !$slugHelper->isUniqueInPid($slug, $state)) {
            $uniqueSlug = $slugHelper->buildSlugForUniqueInPid($slug, $state);
        }
        if (!$uniqueSlug && !$slugHelper->isUniqueInTable($slug, $state)) {
            $uniqueSlug = $slugHelper->buildSlugForUniqueInTable($slug, $state);
        }
        $uniqueSlug = $uniqueSlug ?: $slug;

        return $uniqueSlug;
    }

    private function getRealurlAliasByRecord(array $record): string
    {
        /* @var $queryBuilder \TYPO3\CMS\Core\Database\Query\QueryBuilder */
        $queryBuilder = $this->getConnectionForTable('tx_realurl_uniqalias')->createQueryBuilder();
        $queryBuilder->getRestrictions()->removeAll();

        $queryBuilder
            ->select('value_alias')
            ->from('tx_realurl_uniqalias')
            ->setMaxResults(1);

        $where = 'tablename = :table AND value_id = :uid';
        $queryBuilder
            ->setParameter('table', $this->table)
            ->setParameter('uid', (int) $record['uid']);
        if ($record[$GLOBALS['TCA'][$this->table]['ctrl']['languageField']]) {
            $where .= ' AND lang = :languageUid';
            $queryBuilder->setParameter('languageUid', $record[$GLOBALS['TCA'][$this->table]['ctrl']['languageField']]);
        }

        return (string) ($queryBuilder->where($where)->execute()->fetchAssociative()['value_alias'] ?? '');
    }
}
