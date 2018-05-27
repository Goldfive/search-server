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
 * @author PuntMig Technologies
 */

declare(strict_types=1);

namespace Apisearch\Server\Domain\Repository\Repository;

use Apisearch\Config\Config;
use Apisearch\Config\ImmutableConfig;
use Apisearch\Exception\ResourceExistsException;
use Apisearch\Exception\ResourceNotAvailableException;
use Apisearch\Exception\TransportableException;
use Apisearch\Model\Changes;
use Apisearch\Model\Item;
use Apisearch\Model\ItemUUID;
use Apisearch\Query\Query;
use Apisearch\Repository\Repository as BaseRepository;
use Apisearch\Result\Result;
use Apisearch\Server\Domain\Repository\WithRepositories;

/**
 * Class Repository.
 */
class Repository extends BaseRepository
{
    use WithRepositories;

    /**
     * Flush items.
     *
     * @param Item[]     $itemsToUpdate
     * @param ItemUUID[] $itemsToDelete
     */
    protected function flushItems(
        array $itemsToUpdate,
        array $itemsToDelete
    ) {
        if (!empty($itemsToUpdate)) {
            $this
                ->getRepository(IndexRepository::class)
                ->addItems($itemsToUpdate);
        }

        if (!empty($itemsToDelete)) {
            $this
                ->getRepository(DeleteRepository::class)
                ->deleteItems($itemsToDelete);
        }
    }

    /**
     * Search across the index types.
     *
     * @param Query $query
     *
     * @return Result
     *
     * @throws ResourceNotAvailableException
     */
    public function query(Query $query): Result
    {
        return $this
            ->getRepository(QueryRepository::class)
            ->query($query);
    }

    /**
     * Create an index.
     *
     * @param ImmutableConfig $config
     *
     * @throws ResourceExistsException
     */
    public function createIndex(ImmutableConfig $config)
    {
        $this
            ->getRepository(IndexRepository::class)
            ->createIndex($config);
    }

    /**
     * Delete an index.
     *
     * @throws ResourceNotAvailableException
     */
    public function deleteIndex()
    {
        $this
            ->getRepository(IndexRepository::class)
            ->deleteIndex();
    }

    /**
     * Reset the index.
     *
     * @throws ResourceNotAvailableException
     */
    public function resetIndex()
    {
        $this
            ->getRepository(IndexRepository::class)
            ->resetIndex();
    }

    /**
     * Checks the index.
     *
     * @return bool
     */
    public function checkIndex(): bool
    {
        try {
            $this
                ->getRepository(IndexRepository::class)
                ->getIndexStats();
        } catch (TransportableException $exception) {
            return false;
        }

        return true;
    }

    /**
     * Config the index.
     *
     * @param Config $config
     *
     * @throws ResourceNotAvailableException
     */
    public function configureIndex(Config $config)
    {
        $this
            ->getRepository(ConfigRepository::class)
            ->configureIndex($config);
    }

    /**
     * Update items.
     *
     * @param Query   $query
     * @param Changes $changes
     */
    public function updateItems(
        Query $query,
        Changes $changes
    )
    {
        // TODO: Implement updateItems() method.
    }
}
