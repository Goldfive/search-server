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

namespace Apisearch\Server\Domain\Repository\EventRepository;

use Apisearch\Event\Event;
use Apisearch\Event\EventRepository as BaseEventRepository;
use Apisearch\Exception\ResourceNotAvailableException;
use Apisearch\Query\Query;
use Apisearch\Repository\RepositoryWithCredentials;
use Apisearch\Result\Events;
use Apisearch\Server\Domain\Repository\WithRepositories;

/**
 * Class Repository.
 */
class Repository extends RepositoryWithCredentials implements BaseEventRepository
{
    use WithRepositories;

    /**
     * Save event.
     *
     * @param Event $event
     *
     * @throws ResourceNotAvailableException
     */
    public function save(Event $event)
    {
        $this
            ->getRepository(IndexRepository::class)
            ->addEvent($event);
    }

    /**
     * Query over events.
     *
     * @param Query    $query
     * @param int|null $from
     * @param int|null $to
     *
     * @return Events
     *
     * @throws ResourceNotAvailableException
     */
    public function query(
        Query $query,
        ? int $from = null,
        ? int $to = null
    ): Events {
        return $this
            ->getRepository(QueryRepository::class)
            ->query(
                $query,
                $from,
                $to
            );
    }
}
