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

use Apisearch\Http\Endpoints;
use Apisearch\Model\AppUUID;
use Apisearch\Model\Token;
use Apisearch\Model\TokenUUID;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\Command\AddToken;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GenerateBasicTokensCommand.
 */
class GenerateBasicTokensCommand extends CommandWithBusAndGodToken
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setDescription('Generate a basic tokens ring')
            ->addArgument(
                'app-id',
                InputArgument::REQUIRED,
                'App id'
            );
    }

    /**
     * Dispatch domain event.
     *
     * @return string
     */
    protected function getHeader(): string
    {
        return 'Create basic tokens';
    }

    /**
     * Dispatch domain event.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return mixed
     */
    protected function dispatchDomainEvent(InputInterface $input, OutputInterface $output)
    {
        $appUUID = AppUUID::createById($input->getArgument('app-id'));
        $godToken = $this->createGodToken($appUUID);

        $this->printInfoMessage(
            $output,
            $this->getHeader(),
            "App ID: <strong>{$appUUID->composeUUID()}</strong>"
        );

        foreach ([
            'admin' => [],
            'query only' => Endpoints::queryOnly(),
            'interaction' => Endpoints::interactionOnly(),
                 ] as $tokenName => $endpoints) {
            $this->generateReadOnlyToken(
                $appUUID,
                $endpoints,
                $tokenName,
                $godToken,
                $output
            );
        }
    }

    /**
     * Get success message.
     *
     * @param InputInterface $input
     * @param mixed          $result
     *
     * @return string
     */
    protected function getSuccessMessage(
        InputInterface $input,
        $result
    ): string {
        return 'Tokens created properly';
    }

    /**
     * Generate readonly token.
     *
     * @param AppUUID         $appUUID
     * @param string[]        $endpoints
     * @param string          $name
     * @param Token           $godToken
     * @param OutputInterface $output
     */
    protected function generateReadOnlyToken(
        AppUUID $appUUID,
        array $endpoints,
        string $name,
        Token $godToken,
        OutputInterface $output
    ) {
        $tokenId = Uuid::uuid4()->toString();

        $this
            ->commandBus
            ->handle(new AddToken(
                RepositoryReference::create($appUUID),
                $godToken,
                new Token(
                    TokenUUID::createById($tokenId),
                    $appUUID,
                    [],
                    [],
                    Endpoints::compose($endpoints)
                )
            ));

        $this->printInfoMessage(
            $output,
            $this->getHeader(),
            "Token with UUID <strong>$tokenId</strong> generated for $name"
        );
    }
}
