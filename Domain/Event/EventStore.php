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

namespace Apisearch\Server\Domain\Event;

use Apisearch\Event\Event;
use Apisearch\Event\EventRepository;
use Apisearch\Repository\RepositoryReference;
use Ramsey\Uuid\Uuid;

/**
 * Class EventStore.
 */
class EventStore
{
    /**
     * @var EventRepository
     *
     * Event repository
     */
    private $eventRepository;

    /**
     * EventStore constructor.
     *
     * @param EventRepository $eventRepository
     */
    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * Set repository reference.
     *
     * @param RepositoryReference $repositoryReference
     */
    public function setRepositoryReference(RepositoryReference $repositoryReference)
    {
        $this
            ->eventRepository
            ->setRepositoryReference($repositoryReference);
    }

    /**
     * Append event.
     *
     * @param DomainEvent $event
     */
    public function append(DomainEvent $event)
    {
        $this
            ->eventRepository
            ->save(
                Event::createByPlainData(
                    Uuid::uuid4()->toString(),
                    str_replace(
                        'Apisearch\Server\Domain\Event\\',
                        '',
                        get_class($event)
                    ),
                    json_encode($event->readableOnlyToArray()),
                    array_merge(
                        $event->indexableToArray(),
                        $event->occurredOnRanges()
                    ),
                    $event->occurredOn()
                )
            );
    }
}
