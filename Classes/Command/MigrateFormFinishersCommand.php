<?php

namespace DMK\Mktools\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Finishers\EmailFinisher;

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
 * Class MigrateFormFinishersCommand.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class MigrateFormFinishersCommand extends Command
{
    /**
     * @var ConnectionPool
     */
    private $connectionPool;

    /**
     * @var FlexFormTools
     */
    private $flexformTools;

    public function __construct(ConnectionPool $connectionPool, FlexFormTools $flexformTools)
    {
        parent::__construct(null);

        $this->connectionPool = $connectionPool;
        $this->flexformTools = $flexformTools;
    }

    protected function configure()
    {
        $this->setDescription('Convert all overridden form finisher configurations in plugins.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $formPlugins = $this->getFormPlugins();
        $progress = new ProgressBar($output, count($formPlugins));

        $io->writeln('Found '.count($formPlugins).' form plugins. Starting migration for those with overridden finisher configurations.');

        $migratedPluginsCount = 0;
        foreach ($formPlugins as $formPlugin) {
            $flexform = GeneralUtility::xml2array($formPlugin['pi_flexform']);
            if (is_array($flexform) && $flexform['data']['sDEF']['lDEF']['settings.overrideFinishers']['vDEF']) {
                $this->migrateOverriddenFinisherConfiguration($formPlugin['uid'], $flexform);
                ++$migratedPluginsCount;
            }
            $progress->advance();
        }

        $io->writeln('');
        $io->writeln('');
        $io->writeln($migratedPluginsCount > 0 ? 'Migration finished with '.$migratedPluginsCount.' plugins.' : 'No plugins found to migrate.');

        return 0;
    }

    private function getFormPlugins(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return $queryBuilder
            ->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'Ctype',
                    $queryBuilder->createNamedParameter('form_formframework', \PDO::PARAM_STR)
                )
            )
            ->execute()
            ->fetchAllAssociative();
    }

    private function migrateOverriddenFinisherConfiguration(int $pluginUid, array $flexform): void
    {
        $emailFinisherIdentifiers = ['EmailToSender', 'EmailToReceiver'];
        foreach ($flexform['data'] as $sheetIdentifier => &$sheetConfiguration) {
            // the first common tab
            if ('sDEF' === $sheetIdentifier) {
                continue;
            }
            foreach ($emailFinisherIdentifiers as $emailFinisherIdentifier) {
                $this->migrateFormatOption($sheetConfiguration, $emailFinisherIdentifier);
                $this->migrateRecipientsOptions($sheetConfiguration, $emailFinisherIdentifier);
            }
        }

        $this->connectionPool->getConnectionForTable('tt_content')->update(
            'tt_content',
            [
                'pi_flexform' => $this->flexformTools->flexArray2Xml($flexform),
            ],
            ['uid' => $pluginUid]
        );
    }

    private function migrateFormatOption(array &$sheetConfiguration, string $emailFinisherIdentifier): void
    {
        if (
            isset($sheetConfiguration['lDEF']['settings.finishers.'.$emailFinisherIdentifier.'.format'])
            && !isset($sheetConfiguration['lDEF']['settings.finishers.'.$emailFinisherIdentifier.'.addHtmlPart'])
        ) {
            $format = $sheetConfiguration['lDEF']['settings.finishers.'.$emailFinisherIdentifier.'.format']['vDEF'];
            $addHtmlPart = empty($format) || EmailFinisher::FORMAT_PLAINTEXT !== $format;
            $sheetConfiguration['lDEF']['settings.finishers.'.$emailFinisherIdentifier.'.addHtmlPart']['vDEF'] = intval($addHtmlPart);
            unset($sheetConfiguration['lDEF']['settings.finishers.'.$emailFinisherIdentifier.'.format']);
        }
    }

    private function migrateRecipientsOptions(array &$sheetConfiguration, string $emailFinisherIdentifier): void
    {
        $recipientOptions = [
            'recipientAddress' => 'recipients',
            'carbonCopyAddress' => 'carbonCopyRecipients',
            'blindCarbonCopyAddress' => 'blindCarbonCopyRecipients',
            'replyToAddress' => 'replyToRecipients',
        ];
        foreach ($recipientOptions as $oldOptionKey => $newOptionKey) {
            if (
                isset($sheetConfiguration['lDEF']['settings.finishers.'.$emailFinisherIdentifier.'.'.$oldOptionKey])
                && !empty($sheetConfiguration['lDEF']['settings.finishers.'.$emailFinisherIdentifier.'.'.$oldOptionKey]['vDEF'])
            ) {
                $recipientElement = ['email' => $sheetConfiguration['lDEF']['settings.finishers.'.$emailFinisherIdentifier.'.'.$oldOptionKey]];
                if ('recipientAddress' == $oldOptionKey) {
                    if (!empty($sheetConfiguration['lDEF']['settings.finishers.'.$emailFinisherIdentifier.'.recipientName']['vDEF'])) {
                        $recipientElement['name'] = $sheetConfiguration['lDEF']['settings.finishers.'.$emailFinisherIdentifier.'.recipientName'];
                    }
                }
                $sheetConfiguration['lDEF']['settings.finishers.'.$emailFinisherIdentifier.'.'.$newOptionKey] = [
                    'el' => [
                        uniqid() => [
                            '_arrayContainer' => [
                                'el' => $recipientElement,
                            ],
                        ],
                    ],
                ];
            }
            unset($sheetConfiguration['lDEF']['settings.finishers.'.$emailFinisherIdentifier.'.'.$oldOptionKey]);
        }
    }
}
