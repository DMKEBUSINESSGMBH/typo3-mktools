<?php

declare(strict_types=1);

namespace DMK\Mktools\Updates;

use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/***************************************************************
 * Copyright notice
 *
 * (c) DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class MigrateOldMktoolsPlugins.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class MigrateOldMktoolsPlugins implements UpgradeWizardInterface, ChattyInterface
{
    /**
     * @var string
     */
    private const TABLE_NAME = 'tt_content';

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var string[]
     */
    private $classMappings = [
        'tx_mktools_action_ShowTemplate' => \DMK\Mktools\Action\ShowTemplateAction::class,
        'tx_mktools_action_FlashMessage' => \DMK\Mktools\Action\FlashMessageAction::class,
        'tx_mktools_action_TsLib' => \DMK\Mktools\Action\TyposcriptLibraryAction::class,
    ];

    public function getIdentifier(): string
    {
        return 'oldMktoolsPluginsMigration';
    }

    public function getTitle(): string
    {
        return 'Migrate old mktools plugins';
    }

    public function getDescription(): string
    {
        return 'Migrate old classes of plugins in tt_content.';
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function executeUpdate(): bool
    {
        $query = $this->getPreparedQueryBuilder()->select('uid', 'pi_flexform')->executeQuery();
        $connection = $this->getConnectionPool()->getConnectionForTable(self::TABLE_NAME);
        $errors = $success = [];
        while ($contentElement = $query->fetchAssociative()) {
            $affectedRows = $connection->update(
                self::TABLE_NAME,
                ['pi_flexform' => $this->replaceOldPluginClasses($contentElement['pi_flexform'])],
                ['uid' => (int) $contentElement['uid']]
            );
            if (!$affectedRows) {
                $errors[] = $contentElement['uid'];
                continue;
            }
            $success[] = $contentElement['uid'];
        }

        $this->output->writeln('The following tt_content UIDs have been updated: '.join(', ', $success));

        if ($errors) {
            $this->output->writeln('The following tt_content UIDs failed to update: '.join(', ', $errors));
        }

        return empty($errors);
    }

    protected function replaceOldPluginClasses(string $haystack): string
    {
        return str_replace(array_keys($this->classMappings), array_values($this->classMappings), $haystack);
    }

    public function updateNecessary(): bool
    {
        $pluginCount = $this->getPreparedQueryBuilder()->count('uid')->executeQuery()->fetchOne();
        $this->output->writeln('Found '.$pluginCount.' plugin(s) to migrate.');

        return (bool) $pluginCount;
    }

    public function getPrerequisites(): array
    {
        return [DatabaseUpdatedPrerequisite::class];
    }

    protected function getPreparedQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll();
        $whereConditionsForOldClasses = [];
        foreach ($this->classMappings as $oldClass => $newClass) {
            $whereConditionsForOldClasses[] = $queryBuilder->expr()->like(
                'pi_flexform',
                $queryBuilder->quote('%'.$oldClass.'%')
            );
        }
        $queryBuilder
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq('list_type', $queryBuilder->createNamedParameter('tx_mktools')),
                $queryBuilder->expr()->or(...$whereConditionsForOldClasses)
            );

        return $queryBuilder;
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
