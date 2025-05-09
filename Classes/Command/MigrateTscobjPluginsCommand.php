<?php

/*
 * Copyright notice
 *
 * (c) DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This file is part of the "mktools" Extension for TYPO3 CMS.
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GNU Lesser General Public License can be found at
 * www.gnu.org/licenses/lgpl.html
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

namespace DMK\Mktools\Command;

use DMK\Mktools\Action\TyposcriptLibraryAction;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class MigrateTscobjPluginsCommand.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class MigrateTscobjPluginsCommand extends Command
{
    public function __construct(private ConnectionPool $connectionPool, private FlexFormTools $flexformTools)
    {
        parent::__construct(null);
    }

    protected function configure()
    {
        $this->setDescription('Convert all tscobj plugins to mktools plugins.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $console = new SymfonyStyle($input, $output);
        $console->title($this->getDescription());

        $tscobjPlugins = $this->getTscobjPlugins();
        $progress = new ProgressBar($output, count($tscobjPlugins));

        foreach ($tscobjPlugins as $tscobjPlugin) {
            $this->migrateTscobjPlugin($tscobjPlugin);
            $progress->advance();
        }

        $console->writeln('');
        $console->writeln('');
        $console->writeln([] !== $tscobjPlugins ? 'Migration finished.' : 'No plugins found to migrate.');

        return 0;
    }

    private function getTscobjPlugins(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();

        return $queryBuilder
            ->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'list_type',
                    $queryBuilder->createNamedParameter('tscobj_pi1', \TYPO3\CMS\Core\Database\Connection::PARAM_STR)
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();
    }

    private function migrateTscobjPlugin(array $tscobjPlugin): void
    {
        $typoScriptPath =
            GeneralUtility::xml2array($tscobjPlugin['pi_flexform'])['data']['sDEF']['lDEF']['object']['vDEF'];

        $this->connectionPool->getConnectionForTable('tt_content')->update(
            'tt_content',
            [
                'pi_flexform' => sprintf($this->getFlexformTemplate(), $typoScriptPath),
                'list_type' => 'tx_mktools',
            ],
            ['uid' => $tscobjPlugin['uid']]
        );
    }

    private function getFlexformTemplate(): string
    {
        return $this->flexformTools->flexArray2Xml(
            [
                'data' => [
                    'sDEF' => [
                        'lDEF' => [
                            'action' => [
                                'vDEF' => TyposcriptLibraryAction::class,
                            ],
                        ],
                    ],
                    's_tssetup' => [
                        'lDEF' => [
                            'flexformTS' => [
                                'vDEF' => 'tslib =&lt; %s',
                            ],
                        ],
                    ],
                ],
            ]
        );
    }
}
