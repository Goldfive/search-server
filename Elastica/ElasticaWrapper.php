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

namespace Apisearch\Server\Elastica;

use Apisearch\Config\ImmutableConfig;
use Apisearch\Exception\ResourceExistsException;
use Apisearch\Exception\ResourceNotAvailableException;
use Apisearch\Repository\RepositoryReference;
use Elastica\Client;
use Elastica\Document;
use Elastica\Exception\Bulk\ResponseException as BulkResponseException;
use Elastica\Exception\ResponseException;
use Elastica\Index;
use Elastica\Query;
use Elastica\Type;
use Elastica\Type\Mapping;

/**
 * Class ElasticaWrapper.
 */
abstract class ElasticaWrapper
{
    /**
     * @var Client
     *
     * Elastica client
     */
    private $client;

    /**
     * Construct.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get item type.
     *
     * @return string
     */
    abstract public function getItemType(): string;

    /**
     * Get index name.
     *
     * @param RepositoryReference $repositoryReference
     *
     * @return string
     */
    abstract public function getIndexName(RepositoryReference $repositoryReference): string;

    /**
     * Get index not available exception.
     *
     * @param string $message
     *
     * @return ResourceNotAvailableException
     */
    abstract public function getIndexNotAvailableException(string $message): ResourceNotAvailableException;

    /**
     * Get index configuration.
     *
     * @param ImmutableConfig $config
     * @param int             $shards
     * @param int             $replicas
     *
     * @return array
     */
    abstract public function getIndexConfiguration(
        ImmutableConfig $config,
        int $shards,
        int $replicas
    ): array;

    /**
     * Build index mapping.
     *
     * @param Mapping         $mapping
     * @param ImmutableConfig $config
     */
    abstract public function buildIndexMapping(
        Mapping $mapping,
        ImmutableConfig $config
    );

    /**
     * Get search index.
     *
     * @param RepositoryReference $repositoryReference
     *
     * @return Index
     */
    public function getIndex(RepositoryReference $repositoryReference): Index
    {
        return $this
            ->client
            ->getIndex($this->getIndexName($repositoryReference));
    }

    /**
     * Get index stats.
     *
     * @param RepositoryReference $repositoryReference
     *
     * @return Index\Stats
     */
    public function getIndexStats(RepositoryReference $repositoryReference): Index\Stats
    {
        try {
            return $this
                ->client
                ->getIndex($this->getIndexName($repositoryReference))
                ->getStats();
        } catch (ResponseException $exception) {
            /*
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw $this->getIndexNotAvailableException($exception->getMessage());
        }
    }

    /**
     * Delete index.
     *
     * @param RepositoryReference $repositoryReference
     *
     * @throws ResourceNotAvailableException
     */
    public function deleteIndex(RepositoryReference $repositoryReference)
    {
        try {
            $searchIndex = $this->getIndex($repositoryReference);
            $searchIndex->clearCache();
            $searchIndex->delete();
        } catch (ResponseException $exception) {
            /*
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw $this->getIndexNotAvailableException($exception->getMessage());
        }
    }

    /**
     * Remove index.
     *
     * @param RepositoryReference $repositoryReference
     *
     * @throws ResourceNotAvailableException
     */
    public function resetIndex(RepositoryReference $repositoryReference)
    {
        try {
            $searchIndex = $this->getIndex($repositoryReference);
            $searchIndex->clearCache();
            $searchIndex->deleteByQuery(new Query\MatchAll());
        } catch (ResponseException $exception) {
            /*
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw $this->getIndexNotAvailableException($exception->getMessage());
        }
    }

    /**
     * Create index.
     *
     * @param RepositoryReference $repositoryReference
     * @param ImmutableConfig     $config
     * @param int                 $shards
     * @param int                 $replicas
     *
     * @throws ResourceExistsException
     */
    public function createIndex(
        RepositoryReference $repositoryReference,
        ImmutableConfig $config,
        int $shards,
        int $replicas
    ) {
        $searchIndex = $this->getIndex($repositoryReference);

        try {
            $searchIndex->create($this->getIndexConfiguration(
                $config,
                $shards,
                $replicas
            ));
        } catch (ResponseException $exception) {
            /*
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw $this->getIndexNotAvailableException($exception->getMessage());
        }
    }

    /**
     * Create index.
     *
     * @param RepositoryReference $repositoryReference
     * @param string              $typeName
     *
     * @return Type
     */
    public function getType(
        RepositoryReference $repositoryReference,
        string $typeName
    ) {
        return $this
            ->getIndex($repositoryReference)
            ->getType($typeName);
    }

    /**
     * Search.
     *
     * @param RepositoryReference $repositoryReference
     * @param Query               $query
     * @param int                 $from
     * @param int                 $size
     *
     * @return array
     */
    public function search(
        RepositoryReference $repositoryReference,
        Query $query,
        int $from,
        int $size
    ): array {
        try {
            $queryResult = $this
                ->getIndex($repositoryReference)
                ->search($query, [
                    'from' => $from,
                    'size' => $size,
                ]);
        } catch (ResponseException $exception) {
            /*
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */

            throw $this->getIndexNotAvailableException($exception->getMessage());
        }

        return [
            'results' => $queryResult->getResults(),
            'suggests' => $queryResult->getSuggests(),
            'aggregations' => $queryResult->getAggregations(),
            'total_hits' => $queryResult->getTotalHits(),
        ];
    }

    /**
     * Refresh.
     *
     * @param RepositoryReference $repositoryReference
     */
    public function refresh(RepositoryReference $repositoryReference)
    {
        $this
            ->getIndex($repositoryReference)
            ->refresh();
    }

    /**
     * Create mapping.
     *
     * @param RepositoryReference $repositoryReference
     * @param ImmutableConfig     $config
     *
     * @throws ResourceExistsException
     */
    public function createIndexMapping(
        RepositoryReference $repositoryReference,
        ImmutableConfig $config
    ) {
        try {
            $itemMapping = new Mapping();
            $itemMapping->setType($this->getType($repositoryReference, $this->getItemType()));
            $this->buildIndexMapping($itemMapping, $config);
            $itemMapping->send();
        } catch (ResponseException $exception) {
            /*
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw $this->getIndexNotAvailableException($exception->getMessage());
        }
    }

    /**
     * Add documents.
     *
     * @param RepositoryReference $repositoryReference
     * @param Document[]          $documents
     *
     * @throws ResourceExistsException
     */
    public function addDocuments(
        RepositoryReference $repositoryReference,
        array $documents
    ) {
        try {
            $this
                ->getType($repositoryReference, $this->getItemType())
                ->addDocuments($documents);
        } catch (BulkResponseException $exception) {
            /*
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw $this->getIndexNotAvailableException($exception->getMessage());
        }
    }

    /**
     * Delete documents by its.
     *
     * @param RepositoryReference $repositoryReference
     * @param string[]            $documentsId
     *
     * @throws ResourceExistsException
     */
    public function deleteDocumentsByIds(
        RepositoryReference $repositoryReference,
        array $documentsId
    ) {
        try {
            $this
                ->getType($repositoryReference, $this->getItemType())
                ->deleteIds($documentsId);
        } catch (BulkResponseException $exception) {
            /*
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw $this->getIndexNotAvailableException($exception->getMessage());
        }
    }

    /**
     * Build specific index reference.
     *
     * @param RepositoryReference $repositoryReference
     * @param string              $prefix
     *
     * @return string
     */
    protected function buildIndexReference(
        RepositoryReference $repositoryReference,
        string $prefix
    ) {
        $appId = $repositoryReference->getAppId();
        $indexId = $repositoryReference->getIndex();
        if ('*' === $indexId) {
            return "{$prefix}_{$appId}_*";
        }

        $splittedIndexId = explode(',', $indexId);

        return implode(',', array_map(function (string $indexId) use ($prefix, $appId) {
            return "{$prefix}_{$appId}_$indexId";
        }, $splittedIndexId));
    }
}
