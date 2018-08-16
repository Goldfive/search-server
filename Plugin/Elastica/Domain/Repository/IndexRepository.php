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

namespace Apisearch\Plugin\Elastica\Domain\Repository;

use Apisearch\Config\ImmutableConfig;
use Apisearch\Model\Coordinate;
use Apisearch\Model\Item;
use Apisearch\Plugin\Elastica\Domain\ElasticaWrapperWithRepositoryReference;
use Apisearch\Server\Domain\Repository\Repository\IndexRepository as IndexRepositoryInterface;
use Elastica\Document;
use Elastica\Document as ElasticaDocument;
use Elastica\Index\Stats;

/**
 * Class IndexRepository.
 */
class IndexRepository extends ElasticaWrapperWithRepositoryReference implements IndexRepositoryInterface
{
    /**
     * Create the index.
     *
     * @param ImmutableConfig $config
     */
    public function createIndex(ImmutableConfig $config)
    {
        is_dir($this->getConfigPath())
            ? chmod($this->getConfigPath(), 0755)
            : @mkdir($this->getConfigPath(), 0755, true);

        $this
            ->elasticaWrapper
            ->createIndex(
                $this->getRepositoryReference(),
                $config,
                $this->repositoryConfig['shards'],
                $this->repositoryConfig['replicas']
            );

        $this
            ->elasticaWrapper
            ->createIndexMapping(
                $this->getRepositoryReference(),
                $config
            );

        $this->refresh();
    }

    /**
     * Delete the index.
     */
    public function deleteIndex()
    {
        $this
            ->elasticaWrapper
            ->deleteIndex($this->getRepositoryReference());

        $this->deleteConfigFolder();
        if (is_dir($this->getConfigPath())) {
            @rmdir($this->getConfigPath());
        }
    }

    /**
     * Reset the index.
     */
    public function resetIndex()
    {
        $this
            ->elasticaWrapper
            ->resetIndex($this->getRepositoryReference());

        $this->refresh();
    }

    /**
     * Get the index stats.
     *
     * @return Stats
     */
    public function getIndexStats(): Stats
    {
        return $this
            ->elasticaWrapper
            ->getIndexStats($this->getRepositoryReference());
    }

    /**
     * Generate items documents.
     *
     * @param Item[] $items
     */
    public function addItems(array $items)
    {
        $documents = [];
        foreach ($items as $item) {
            $documents[] = $this->createItemDocument($item);
        }

        if (empty($documents)) {
            return;
        }

        $this
            ->elasticaWrapper
            ->addDocuments(
                $this->getRepositoryReference(),
                $documents
            );

        $this->refresh();
    }

    /**
     * Create item document.
     *
     * @param Item $item
     *
     * @return Document
     */
    private function createItemDocument(Item $item): Document
    {
        $uuid = $item->getUUID();
        $itemDocument = [
            'uuid' => [
                'id' => $uuid->getId(),
                'type' => $uuid->getType(),
            ],
            'coordinate' => $item->getCoordinate() instanceof Coordinate
                ? $item
                    ->getCoordinate()
                    ->toArray()
                : null,
            'metadata' => $this->filterElementRecursively(
                $item->getMetadata()
            ),
            'indexed_metadata' => $this->filterElementRecursively(
                $item->getIndexedMetadata()
            ),
            'searchable_metadata' => $this->filterSearchableElementRecursively(
                $item->getSearchableMetadata()
            ),
            'exact_matching_metadata' => array_values(
                $this->filterSearchableElementRecursively(
                    $item->getExactMatchingMetadata()
                )
            ),
            'suggest' => array_values(
                $this->filterSearchableElementRecursively(
                    $item->getSuggest()
                )
            ),
        ];

        return new ElasticaDocument($uuid->composeUUID(), $itemDocument);
    }

    /**
     * Filter recursively element for index and data.
     *
     * @param mixed $elements
     *
     * @return mixed $element
     */
    private function filterElementRecursively(array $elements)
    {
        foreach ($elements as $key => $element) {
            if (is_array($element)) {
                $elements[$key] = $this->filterElementRecursively($element);
            }
        }

        $elements = array_filter(
            $elements,
            [$this, 'filterElement']
        );

        return $elements;
    }

    /**
     * Filter element for index and data.
     *
     * @param mixed $element
     *
     * @return mixed $element
     */
    private function filterElement($element)
    {
        return !(
            is_null($element) ||
            (is_array($element) && empty($element))
        );
    }

    /**
     * Filter element for search.
     *
     * @param array $elements
     *
     * @return mixed $element
     */
    private function filterSearchableElementRecursively(array $elements)
    {
        foreach ($elements as $key => $element) {
            if (is_array($element)) {
                $elements[$key] = $this->filterSearchableElementRecursively($element);
            }
        }

        $elements = array_filter(
            $elements,
            [$this, 'filterSearchableElement']
        );

        return $elements;
    }

    /**
     * Filter element for search.
     *
     * @param mixed $element
     *
     * @return mixed $element
     */
    private function filterSearchableElement($element)
    {
        return !(
            is_null($element) ||
            is_bool($element) ||
            (is_string($element) && empty($element)) ||
            (is_array($element) && empty($element))
        );
    }

    /**
     * Delete all config folder.
     */
    private function deleteConfigFolder()
    {
        $files = glob($this->getConfigPath().'/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
