<?php

namespace DMK\Mktools\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
 * Class MigrateTcaFileGroupToFalCommand.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class MigrateTcaFileGroupToFalCommand extends Command
{
    /**
     * @var ConnectionPool
     */
    private $connectionPool;

    /**
     * @var ResourceFactory
     */
    private $resourceFactory;

    public function __construct(ConnectionPool $connectionPool, ResourceFactory $resourceFactory)
    {
        parent::__construct(null);

        $this->connectionPool = $connectionPool;
        $this->resourceFactory = $resourceFactory;
    }

    protected function configure()
    {
        $this->setDescription('Convert TCA field of type group with internal_type file to FAL references. You must change the TCA configuration for the fields accordingly after the migration.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
        $progress = new ProgressBar($output);
        $progress->setFormat(' %current% migrated rows [%bar%] %elapsed:6s% %memory:6s%');

        $migratedFields = [];
        foreach ($GLOBALS['TCA'] as $table => $tableDefintion) {
            foreach ($tableDefintion['columns'] ?? [] as $field => $fieldDefintion) {
                if ('group' == ($fieldDefintion['config']['type'] ?? '') && 'file' == ($fieldDefintion['config']['internal_type'] ?? '')) {
                    $rows = $this->getRowsWithFileGroup($table, $field);
                    $migratedFields[$table][$field] = count($rows);
                    foreach ($rows as $row) {
                        $this->migrateFileGroupToFal($table, $field, $row, $fieldDefintion);
                        $progress->advance();
                        exit;
                    }
                }
            }
        }

        $io->writeln('');
        $io->writeln('');
        $io->writeln('The following fields have been found and migrated:');
        foreach ($migratedFields as $table => $field) {
            $io->writeln('Table: '.$table.'; Field: '.key($field).'; Number of affected rows: '.current($field));
        }
        $io->writeln('You should change the TCA definition of those fields like this:');
        $io->writeln('');
        $io->writeln('\'config\' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
    \'images\',
    [
        \'behaviour\' => [
            \'allowLanguageSynchronization\' => true,
        ],
        \'appearance\' => [
            \'createNewRelationLinkTitle\' => \'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference\',
            \'showPossibleLocalizationRecords\' => true,
            \'showRemovedLocalizationRecords\' => true,
            \'showAllLocalizationLink\' => true,
            \'showSynchronizationLink\' => true,
        ],
        \'foreign_match_fields\' => [
            \'fieldname\' => \'images\',
            \'tablenames\' => \'sys_category\',
            \'table_local\' => \'sys_file\',
        ],
    ],
    $GLOBALS[\'TYPO3_CONF_VARS\'][\'GFX\'][\'imagefile_ext\']
)');
        $io->writeln('');
        $io->writeln('Furthermore you need to change the code which retrieves and renders the files.');

        return 0;
    }

    private function getRowsWithFileGroup(string $table, string $field): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();

        return $queryBuilder
            ->select('*')
            ->from($table)
            ->where($queryBuilder->expr()->andX(
                $queryBuilder->expr()->neq($field, $queryBuilder->createNamedParameter('')),
                $queryBuilder->expr()->neq($field, $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->isNotNull($field)
            ))
            ->execute()
            ->fetchAllAssociative();
    }

    private function migrateFileGroupToFal(string $table, string $field, array $record, array $fieldDefintion): void
    {
        $index = 0;
        $this->deleteFileReferences($table, $field, intval($record['uid']));
        foreach (GeneralUtility::trimExplode(',', $record[$field]) as $file) {
            $filePath = $fieldDefintion['config']['uploadfolder'].'/'.$file;
            try {
                $file = $this->resourceFactory->retrieveFileOrFolderObject($filePath);
                $this->insertFileReference($table, $field, $record, $file, $index);
                ++$index;
            } catch (FolderDoesNotExistException $exception) {
                continue;
            }
        }
    }

    private function deleteFileReferences(string $table, string $field, int $uidForeign): void
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_file_reference');
        $queryBuilder
            ->delete('sys_file_reference')
            ->where(
                $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($uidForeign, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('fieldname', $queryBuilder->createNamedParameter($field)),
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter($table)),
            )
            ->execute();
    }

    private function insertFileReference(string $table, string $field, array $record, File $file, int $sorting): void
    {
        $this->connectionPool->getQueryBuilderForTable('sys_file_reference')
            ->insert('sys_file_reference')
            ->values([
                'fieldname' => $field,
                'table_local' => 'sys_file',
                'pid' => 'pages' === $table ? $record['uid'] : $record['pid'],
                'uid_foreign' => $record['uid'],
                'uid_local' => $file->getUid(),
                'tablenames' => $table,
                'crdate' => time(),
                'tstamp' => time(),
                'sorting' => $sorting + 256,
                'sorting_foreign' => $sorting,
            ])
            ->execute();
    }
}
