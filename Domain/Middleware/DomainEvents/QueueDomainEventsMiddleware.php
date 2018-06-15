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

namespace Apisearch\Server\Domain\Middleware\DomainEvents;

use Apisearch\Repository\WithRepositoryReference;
use Apisearch\Server\Domain\Event\DomainEvent;
use Apisearch\Server\Domain\Event\EventPublisher;
use League\Tactician\Middleware;
use RSQueue\Services\Producer as QueueProducer;

/**
 * Class QueueDomainEventsMiddleware.
 */
class QueueDomainEventsMiddleware extends DomainEventsMiddleware implements Middleware
{
    /**
     * @var QueueProducer
     *
     * Queue producer
     */
    private $queueProducer;

    /**
     * DomainEventsMiddleware constructor.
     *
     * @param EventPublisher $eventPublisher
     * @param QueueProducer  $queueProducer
     */
    public function __construct(
        EventPublisher $eventPublisher,
        QueueProducer $queueProducer
    ) {
        parent::__construct($eventPublisher);

        $this->queueProducer = $queueProducer;
    }

    /**
     * Process events.
     *
     * @param WithRepositoryReference $command
     * @param DomainEvent             $event
     */
    public function processEvent(
        WithRepositoryReference $command,
        DomainEvent $event
    ) {
        $repositoryReference = $command->getRepositoryReference();
        $this
            ->queueProducer
            ->produce(
                'apisearch:server:domain-events',
                [
                    'app_id' => $repositoryReference->getAppId(),
                    'index_id' => $repositoryReference->getIndex(),
                    'event' => $event->toArray(),
                ]
            );
    }
}
