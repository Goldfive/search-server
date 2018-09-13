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

namespace Apisearch\Server\Domain\Repository\AppRepository;

use Apisearch\App\AppRepository as BaseRepository;
use Apisearch\Config\Config;
use Apisearch\Exception\ResourceExistsException;
use Apisearch\Exception\ResourceNotAvailableException;
use Apisearch\Exception\TransportableException;
use Apisearch\Model\Index;
use Apisearch\Model\IndexUUID;
use Apisearch\Model\Token;
use Apisearch\Model\TokenUUID;
use Apisearch\Repository\RepositoryWithCredentials;
use Apisearch\Server\Domain\Repository\WithRepositories;

/**
 * Class Repository.
 */
class Repository extends RepositoryWithCredentials implements BaseRepository
{
    use WithRepositories;

    /**
     * Add token.
     *
     * @param Token $token
     */
    public function addToken(Token $token)
    {
        $this
            ->getRepository(TokenRepository::class)
            ->addToken($token);
    }

    /**
     * Delete token.
     *
     * @param TokenUUID $tokenUUID
     */
    public function deleteToken(TokenUUID $tokenUUID)
    {
        $this
            ->getRepository(TokenRepository::class)
            ->deleteToken($tokenUUID);
    }

    /**
     * Get tokens.
     *
     * @return Token[]
     */
    public function getTokens(): array
    {
        return $this
            ->getRepository(TokenRepository::class)
            ->getTokens();
    }

    /**
     * Delete all tokens.
     */
    public function deleteTokens()
    {
        $this
            ->getRepository(TokenRepository::class)
            ->deleteTokens();
    }

    /**
     * Get indices.
     *
     * @return Index[]
     */
    public function getIndices(): array
    {
        return $this
            ->getRepository(IndexRepository::class)
            ->getIndices();
    }

    /**
     * Create an index.
     *
     * @param IndexUUID       $indexUUID
     * @param Config $config
     *
     * @throws ResourceExistsException
     */
    public function createIndex(
        IndexUUID $indexUUID,
        Config $config
    ) {
        return $this
            ->getRepository(IndexRepository::class)
            ->createIndex(
                $indexUUID,
                $config
            );
    }

    /**
     * Delete an index.
     *
     * @param IndexUUID $indexUUID
     *
     * @throws ResourceNotAvailableException
     */
    public function deleteIndex(IndexUUID $indexUUID)
    {
        $this
            ->getRepository(IndexRepository::class)
            ->deleteIndex($indexUUID);
    }

    /**
     * Reset the index.
     *
     * @param IndexUUID $indexUUID
     *
     * @throws ResourceNotAvailableException
     */
    public function resetIndex(IndexUUID $indexUUID)
    {
        $this
            ->getRepository(IndexRepository::class)
            ->resetIndex($indexUUID);
    }

    /**
     * Checks the index.
     *
     * @param IndexUUID $indexUUID
     *
     * @return bool
     */
    public function checkIndex(IndexUUID $indexUUID): bool
    {
        try {
            $this
                ->getRepository(IndexRepository::class)
                ->getIndexStats($indexUUID);
        } catch (TransportableException $exception) {
            return false;
        }

        return true;
    }

    /**
     * Config the index.
     *
     * @param IndexUUID $indexUUID
     * @param Config    $config
     *
     * @throws ResourceNotAvailableException
     */
    public function configureIndex(
        IndexUUID $indexUUID,
        Config $config
    ) {
        $this
            ->getRepository(ConfigRepository::class)
            ->configureIndex(
                $indexUUID,
                $config
            );
    }
}
