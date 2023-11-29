<?php

namespace DMK\Mktools\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Information\Typo3Version;
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
 * Class MigrateSwitchableControllerActionsCommand.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class MigrateSwitchableControllerActionsCommand extends Command
{
    private ConnectionPool $connectionPool;

    public function __construct(ConnectionPool $connectionPool)
    {
        parent::__construct(null);

        $this->connectionPool = $connectionPool;
    }

    protected function configure()
    {
        $this->setDescription('Migrate all plugins with the given switchableControllerActions and list_type to the given new list_type. Permissions in BE groups are migrated as well.')
            ->addOption(
                'actions',
                'a',
                InputOption::VALUE_REQUIRED,
                'switchableControllerActions as used in the Flexform for example.'
            )->addOption(
                'list-type',
                'l',
                InputOption::VALUE_REQUIRED,
                'The old list type before separating all plugins'
            )->addOption(
                'new-list-type',
                'nl',
                InputOption::VALUE_REQUIRED,
                'The new list type for the given switchableControllerActions.'
            )->addOption(
                'cleanup-flexform',
                'cl',
                InputOption::VALUE_OPTIONAL,
                'Should the flexform be cleaned up so only values from the configured flexform are kept? This doesn\'t take flexform extensions into consideration. So if you extend the flexform of e.g. calendarize you shouldn\'t cleanup the flexform'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('list-type') || !$input->getOption('actions') || !$input->getOption('new-list-type')) {
            $output->writeln('Please provide all options. See help for more information.');

            return 1;
        }

        $this->migratePlugins($input, $output);
        $this->migrateBackendUserGroups($input, $output);

        return 0;
    }

    protected function migratePlugins(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
        $progress = new ProgressBar($output);
        $progress->setFormat(' %current% migrated rows [%bar%] %elapsed:6s% %memory:6s%');

        $contentElements = $this->getContentElementsToMigrate($input->getOption('list-type'), $input->getOption('actions'));

        $io->writeln('Start migration of '.count($contentElements).' plugins.');

        foreach ($contentElements as $contentElement) {
            $flexFormData = GeneralUtility::xml2array($contentElement['pi_flexform']);
            $newListType = $input->getOption('new-list-type');
            if ($input->getOption('cleanup-flexform')) {
                $flexFormData = $this->removeFlexFormSettingsNotForListType($flexFormData, $newListType);
            }

            if (count($flexFormData['data']) > 0) {
                $newFlexform = $this->array2xml($flexFormData);
            } else {
                $newFlexform = '';
            }

            $this->updateContentElement($contentElement['uid'], $newListType, $newFlexform);

            $progress->advance();
        }

        $io->writeln('');
        $io->writeln('');
        $io->writeln('Migration finished');
    }

    protected function getContentElementsToMigrate(string $listType, string $actions): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return $queryBuilder
            ->select('uid', 'list_type', 'pi_flexform')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'list_type',
                    $queryBuilder->createNamedParameter($listType)
                ),
                $queryBuilder->expr()->like(
                    'pi_flexform',
                    $queryBuilder->createNamedParameter(
                        '%>'.$queryBuilder->escapeLikeWildcards(htmlspecialchars($actions)).'<%'
                    )
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();
    }

    protected function removeFlexFormSettingsNotForListType(array $flexFormData, string $newListType): array
    {
        $allowedSettings = $this->getAllowedSettingsFromFlexFormByListType($newListType);

        // Remove flexform data which do not exist in flexform of new plugin
        foreach ($flexFormData['data'] as $sheetKey => $sheetData) {
            if (($sheetData['lDEF'] ?? null) && is_array($sheetData['lDEF'])) {
                foreach ($sheetData['lDEF'] as $settingName => $setting) {
                    if (!in_array($settingName, $allowedSettings, true)) {
                        unset($flexFormData['data'][$sheetKey]['lDEF'][$settingName]);
                    }
                }
            }

            // Remove empty sheets
            if (!is_array($flexFormData['data'][$sheetKey]['lDEF']) || !count($flexFormData['data'][$sheetKey]['lDEF']) > 0) {
                unset($flexFormData['data'][$sheetKey]);
            }
        }

        return $flexFormData;
    }

    protected function getAllowedSettingsFromFlexFormByListType(string $listType): array
    {
        $flexFormFile = $GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['config']['ds'][$listType.',list'] ?? '';
        if (!$flexFormFile) {
            return [];
        }

        $flexFormContent = file_get_contents(GeneralUtility::getFileAbsFileName(substr(trim($flexFormFile), 5)));
        $flexFormData = GeneralUtility::xml2array($flexFormContent);

        // Iterate each sheet and extract all settings
        $settings = [];
        foreach ($flexFormData['sheets'] as $sheet) {
            foreach ($sheet['ROOT']['el'] as $setting => $tceForms) {
                $settings[] = $setting;
            }
        }

        return $settings;
    }

    protected function array2xml(array $input = []): string
    {
        $options = [
            'parentTagMap' => [
                'data' => 'sheet',
                'sheet' => 'language',
                'language' => 'field',
                'el' => 'field',
                'field' => 'value',
                'field:el' => 'el',
                'el:_IS_NUM' => 'section',
                'section' => 'itemType',
            ],
            'disableTypeAttrib' => 2,
        ];
        $spaceInd = 4;
        $output = GeneralUtility::array2xml($input, '', 0, 'T3FlexForms', $spaceInd, $options);

        return '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>'.LF.$output;
    }

    protected function updateContentElement(int $uid, string $newListType, string $flexform): void
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->update('tt_content')
            ->set('list_type', $newListType)
            ->set('pi_flexform', $flexform)
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)
                )
            )
            ->executeStatement();
    }

    protected function migrateBackendUserGroups(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
        $progress = new ProgressBar($output);
        $progress->setFormat(' %current% migrated rows [%bar%] %elapsed:6s% %memory:6s%');

        $groups = $this->getBackendUserGroupsToMigrate($input->getOption('list-type'));

        $io->writeln('Start migration of '.count($groups).' BE groups.');

        foreach ($groups as $group) {
            $this->updateBackendUserGroup($group, $input->getOption('list-type'), $input->getOption('new-list-type'));
            $progress->advance();
        }

        $io->writeln('');
        $io->writeln('');
        $io->writeln('Migration finished');
    }

    protected function getBackendUserGroupsToMigrate(string $listType): array
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('be_groups');
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return $queryBuilder
            ->select('uid', 'explicit_allowdeny')
            ->from('be_groups')
            ->where(
                $queryBuilder->expr()->like(
                    'explicit_allowdeny',
                    $queryBuilder->createNamedParameter('%'.$queryBuilder->escapeLikeWildcards('tt_content:list_type:'.$listType).'%')
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();
    }

    protected function updateBackendUserGroup(array $row, string $listType, string $newListType): void
    {
        $default = 'tt_content:list_type:'.$listType.',tt_content:list_type:'.$newListType;

        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() >= 12) {
            $searchReplace = [
                'tt_content:list_type:'.$listType.':ALLOW' => $default,
                'tt_content:list_type:'.$listType.':DENY' => '',
                'tt_content:list_type:'.$listType => $default,
            ];
        } else {
            $default .= ',';
            $default = str_replace(',', ':ALLOW,', $default);
            $searchReplace = [
                'tt_content:list_type:'.$listType.':ALLOW' => $default,
                'tt_content:list_type:'.$listType.':DENY' => str_replace($default, 'ALLOW', 'DENY'),
            ];
        }

        $newList = str_replace(array_keys($searchReplace), array_values($searchReplace), $row['explicit_allowdeny']);
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('be_groups');
        $queryBuilder->update('be_groups')
            ->set('explicit_allowdeny', $newList)
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($row['uid'], Connection::PARAM_INT)
                )
            )
            ->executeStatement();
    }
}
