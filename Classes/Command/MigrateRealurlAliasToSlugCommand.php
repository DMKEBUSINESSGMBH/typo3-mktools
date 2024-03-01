<?php

namespace DMK\Mktools\Command;

use DMK\Mktools\Utility\SlugUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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
 * Class MigrateRealurlAliasToSlugCommand.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class MigrateRealurlAliasToSlugCommand extends Command
{
    protected function configure()
    {
        $this->setDescription('Migrating realurl alias to slugs. Records without alias will have a slug generated.')
            ->addOption(
                'table',
                't',
                InputOption::VALUE_REQUIRED,
                'Table to migrate the slugs in.'
            )->addOption(
                'field',
                'f',
                InputOption::VALUE_REQUIRED,
                'Field that holds the slugs.'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = $input->getOption('table');
        $field = $input->getOption('field');

        $output->writeln('');
        $output->writeln('Start realurl alias to slug migration in table '.$table.' for field '.$field.'. If no alias is found the slug is generated.');
        GeneralUtility::makeInstance(SlugUtility::class, $table, $field)->migrateRealurlAliasToSlug();
        $output->writeln('Migration finished...');
        $output->writeln('');

        return 0;
    }
}
