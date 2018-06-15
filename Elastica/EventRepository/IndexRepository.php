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

namespace Apisearch\Server\Elastica\EventRepository;

use Apisearch\Event\Event;
use Apisearch\Server\Domain\Repository\EventRepository\IndexRepository as IndexRepositoryInterface;
use Apisearch\Server\Elastica\Builder\TimeFormatBuilder;
use Apisearch\Server\Elastica\ElasticaWrapper;
use Apisearch\Server\Elastica\ElasticaWrapperWithRepositoryReference;
use Elastica\Document;
use Elastica\Document as ElasticaDocument;

/**
 * Class IndexRepository.
 */
class IndexRepository extends ElasticaWrapperWithRepositoryReference implements IndexRepositoryInterface
{
    /**
     * @var TimeFormatBuilder
     *
     * Time format builder
     */
    private $timeFormatBuilder;

    /**
     * ElasticaSearchRepository constructor.
     *
     * @param ElasticaWrapper   $elasticaWrapper
     * @param array             $repositoryConfig
     * @param TimeFormatBuilder $timeFormatBuilder
     */
    public function __construct(
        ElasticaWrapper $elasticaWrapper,
        array $repositoryConfig,
        TimeFormatBuilder $timeFormatBuilder
    ) {
        parent::__construct(
            $elasticaWrapper,
            $repositoryConfig
        );

        $this->timeFormatBuilder = $timeFormatBuilder;
    }

    /**
     * Generate event document.
     *
     * @param Event $event
     */
    public function addEvent(Event $event)
    {
        $this
            ->elasticaWrapper
            ->addDocuments(
                $this->normalizeRepositoryReferenceCrossIndices(
                    $this->getRepositoryReference()
                ),
                [$this->createEventDocument($event)]
            );

        $this->refresh();
    }

    /**
     * Create item document.
     *
     * @param Event $event
     *
     * @return Document
     */
    private function createEventDocument(Event $event): Document
    {
        $formattedTime = $this
            ->timeFormatBuilder
            ->formatTimeFromMillisecondsToBasicDateTime(
                $event->getOccurredOn()
            );

        $itemDocument = [
            'uuid' => [
                'id' => $event->getConsistencyHash(),
                'type' => $event->getName(),
            ],
            'payload' => $event->getPayload(),
            'indexed_metadata' => [
                'occurred_on' => $formattedTime,
            ] + $event->getIndexablePayload(),
        ];

        return new ElasticaDocument(
            $event->getConsistencyHash(),
            $itemDocument
        );
    }
}
