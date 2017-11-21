<?php

/*
 * This file is part of the Search Server Bundle.
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

namespace Puntmig\Search\Server\Domain\Event;

use Puntmig\Search\Event\Event;
use Puntmig\Search\Event\EventRepository;

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
     * Set app id.
     *
     * @param string $appId
     */
    public function setAppId(string $appId)
    {
        $this
            ->eventRepository
            ->setAppId($appId);
    }

    /**
     * Append event.
     *
     * @param DomainEvent      $event
     * @param null|DomainEvent $previousEvent
     */
    public function append(
        DomainEvent $event,
        ? DomainEvent $previousEvent = null
    ) {
        $this
            ->eventRepository
            ->save(
                Event::createByPreviousEvent(
                    $previousEvent ?? $this
                        ->eventRepository
                        ->last(),
                    str_replace(
                        'Puntmig\Search\Server\Domain\Event\\',
                        '',
                        get_class($event)
                    ),
                    json_encode($event->payloadToArray()),
                    $event->occurredOn()
                )
            );
    }

    /**
     * Get all domain events.
     *
     * @param int|null $from
     * @param int|null $to
     * @param int|null $length
     * @param int|null $offset
     *
     * @return DomainEvent[]
     */
    public function allDomainEvents(
        ?int $from = null,
        ?int $to = null,
        ?int $length = 10,
        ?int $offset = 0
    ): array {
        return array_map(function (Event $event) {
            return DomainEvent::fromArray([
                'type' => $event->getName(),
                'occurred_on' => $event->getOccurredOn(),
                'payload' => $event->getPayload(),
            ]);
        }, $this
            ->eventRepository
            ->all(
                null,
                $from,
                $to,
                $length,
                $offset
            )
        );
    }
}
