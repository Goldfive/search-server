<?php

/*
 * This file is part of the Apisearch Server
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace Apisearch\Server\Console;

use Apisearch\Config\Config;
use Apisearch\Config\Synonym;
use Apisearch\Config\SynonymReader;
use Apisearch\Exception\ResourceExistsException;
use Apisearch\Server\Domain\Command\CreateIndex;
use League\Tactician\CommandBus;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateIndexCommand.
 */
class CreateIndexCommand extends CommandWithBusAndGodToken
{
    /**
     * @var SynonymReader
     *
     * Synonym Reader
     */
    private $synonymReader;

    /**
     * CreateIndexCommand constructor.
     *
     *
     * @param CommandBus    $commandBus
     * @param string        $godToken
     * @param SynonymReader $synonymReader
     */
    public function __construct(
        CommandBus $commandBus,
        string     $godToken,
        SynonymReader $synonymReader
    ) {
        parent::__construct(
            $commandBus,
            $godToken
        );

        $this->synonymReader = $synonymReader;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setDescription('Create an index')
            ->addArgument(
                'app-id',
                InputArgument::REQUIRED,
                'App id'
            )
            ->addArgument(
                'index',
                InputArgument::REQUIRED,
                'Index'
            )
            ->addOption(
                'language',
                null,
                InputOption::VALUE_OPTIONAL,
                'Index language',
                null
            )
            ->addOption(
                'no-store-searchable-metadata',
                null,
                InputOption::VALUE_NONE,
                'Store searchable metadata'
            )
            ->addOption(
                'synonym',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Synonym'
            )
            ->addOption(
                'synonyms-file',
                null,
                InputOption::VALUE_OPTIONAL,
                'Synonyms file',
                ''
            )
            ->addOption(
                'shards',
                null,
                InputOption::VALUE_OPTIONAL,
                'Shards for the index',
                Config::DEFAULT_SHARDS
            )
            ->addOption(
                'replicas',
                null,
                InputOption::VALUE_OPTIONAL,
                'Replicas for the index',
                Config::DEFAULT_REPLICAS
            );
    }

    /**
     * Dispatch domain event.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return mixed|null
     */
    protected function runCommand(InputInterface $input, OutputInterface $output)
    {
        $objects = $this->getAppIndexToken($input, $output);
        $synonyms = $this
            ->synonymReader
            ->readSynonymsFromFile($input->getOption('synonyms-file'));

        $synonyms += $this
            ->synonymReader
            ->readSynonymsFromCommaSeparatedArray($input->getOption('synonym'));

        try {
            $this
                ->commandBus
                ->handle(new CreateIndex(
                    $objects['repository_reference'],
                    $objects['token'],
                    $objects['index_uuid'],
                    Config::createFromArray([
                        'language' => $input->getOption('language'),
                        'store_searchable_metadata' => !$input->getOption('no-store-searchable-metadata'),
                        'synonyms' => $synonyms = array_map(function (Synonym $synonym) {
                            return $synonym->toArray();
                        }, $synonyms),
                        'shards' => $input->getOption('shards'),
                        'replicas' => $input->getOption('replicas'),
                    ])
                ));
        } catch (ResourceExistsException $exception) {
            $this->printInfoMessage(
                $output,
                $this->getHeader(),
                'Index is already created. Skipping.'
            );
        }
    }

    /**
     * Dispatch domain event.
     *
     * @return string
     */
    protected static function getHeader(): string
    {
        return 'Create index';
    }

    /**
     * Get success message.
     *
     * @param InputInterface $input
     * @param mixed          $result
     *
     * @return string
     */
    protected static function getSuccessMessage(
        InputInterface $input,
        $result
    ): string {
        return 'Index created properly';
    }
}
