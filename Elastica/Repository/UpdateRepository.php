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

namespace Apisearch\Server\Elastica\Repository;

use Apisearch\Model\Changes;
use Apisearch\Model\Item;
use Apisearch\Query\Query;
use Apisearch\Server\Domain\Repository\Repository\UpdateRepository as UpdateRepositoryInterface;
use Apisearch\Server\Elastica\Builder\QueryBuilder;
use Apisearch\Server\Elastica\ElasticaWrapper;
use Apisearch\Server\Elastica\ElasticaWrapperWithRepositoryReference;
use Elastica\Query as ElasticaQuery;
use Elastica\Script\AbstractScript;
use Elastica\Script\Script;

/**
 * Class UpdateRepository.
 */
class UpdateRepository extends ElasticaWrapperWithRepositoryReference implements UpdateRepositoryInterface
{
    /**
     * @var QueryBuilder
     *
     * Query builder
     */
    private $queryBuilder;

    /**
     * ElasticaSearchRepository constructor.
     *
     * @param ElasticaWrapper $elasticaWrapper
     * @param array           $repositoryConfig
     * @param QueryBuilder    $queryBuilder
     */
    public function __construct(
        ElasticaWrapper $elasticaWrapper,
        array $repositoryConfig,
        QueryBuilder $queryBuilder
    ) {
        parent::__construct(
            $elasticaWrapper,
            $repositoryConfig
        );

        $this->queryBuilder = $queryBuilder;
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
    ) {
        $mainQuery = new ElasticaQuery();
        $boolQuery = new ElasticaQuery\BoolQuery();
        $this
            ->queryBuilder
            ->buildQuery(
                $query,
                $mainQuery,
                $boolQuery
            );

        $this
            ->elasticaWrapper
            ->getIndex($this->getRepositoryReference())
            ->updateByQuery(
                $mainQuery,
                $this->createUpdateScriptByChanges($changes),
                [
                    'conflicts' => 'proceed',
                ]
            );
        $this->refresh();
    }

    /**
     * Build update script by Changes.
     *
     * @param Changes $changes
     *
     * @return AbstractScript|null
     */
    private function createUpdateScriptByChanges(Changes $changes): ? AbstractScript
    {
        if (empty($changes->getChanges())) {
            return null;
        }

        $bucleScripts = [];
        $singleScripts = [];
        $params = [];
        foreach ($changes->getChanges() as $change) {
            $field = $change['field'];
            $internalField = $this->parseExpressionToInternal($field);
            $currentScript = null;
            $currentValue = null;
            $type = $change['type'];

            if ($type & Changes::TYPE_VALUE) {
                $fieldName = 'param_'.str_replace('.', '_', $field).'_'.rand(0, 99999999999);
                $currentValue = "params.$fieldName";
                $currentScript = "$internalField = $currentValue;";
                $params[$fieldName] = $change['value'];
            }

            if ($type & Changes::TYPE_LITERAL) {
                $currentValue = $this->parseExpressionToInternal($change['value']);
                $currentScript = "$internalField = $currentValue;";
            }

            if ($type & Changes::TYPE_ARRAY) {
                if (
                    ($type & Changes::TYPE_ARRAY_EXPECTS_ELEMENT) &&
                    empty($currentValue)
                ) {
                    continue;
                }

                $condition = isset($change['condition']) && !empty($change['condition'])
                    ? $this->parseExpressionToInternal($change['condition'])
                    : null;

                $assignmentLine = null;

                if ($type & Changes::TYPE_ARRAY_ELEMENT_ADD) {
                    $singleScripts[] = "{$internalField}.add($currentValue);";
                    continue;
                } elseif ($type & Changes::TYPE_ARRAY_ELEMENT_DELETE) {
                    $assignmentLine = "{$internalField}.remove(i);";
                } elseif ($type & Changes::TYPE_ARRAY_ELEMENT_UPDATE) {
                    $assignmentLine = "{$internalField}.set(i, $currentValue);";
                }

                if (is_null($assignmentLine)) {
                    continue;
                }

                if (!is_null($condition)) {
                    $assignmentLine = "    if ($condition) {
        $assignmentLine
    }";
                }

                if (!isset($bucleScripts[$internalField])) {
                    $bucleScripts[$internalField] = [];
                }

                $bucleScripts[$internalField][] = $assignmentLine;

                continue;
            }

            $singleScripts[] = $currentScript;
        }

        $finalScript = 'def item = ctx._source;
def element;'.PHP_EOL;

        $finalScript .= implode(PHP_EOL, $singleScripts).PHP_EOL;
        foreach ($bucleScripts as $bucleInternalField => $bucleScriptElements) {
            $rand = rand(0, 100000000000000);
            $finalScript .= "def field_{$rand} = $bucleInternalField;
if (field_$rand != null && field_$rand instanceof Collection) {
    for (int i = 0; i < field_$rand.length; i++) {
        element = field_{$rand}[i];".PHP_EOL;

            foreach ($bucleScriptElements as $bucleScriptElement) {
                $finalScript .= $bucleScriptElement.PHP_EOL;
            }

            $finalScript .= '}}'.PHP_EOL;
        }

        $finalScript = trim($finalScript);

        return empty($finalScript)
            ? null
            : new Script(
                $finalScript,
                $params
            );
    }

    /**
     * Parse expression with internal format.
     *
     * @param string $expression
     *
     * @return string
     */
    private function parseExpressionToInternal(string $expression): string
    {
        return preg_replace(
            '~((?:(?:indexed|searchable|exact_matching)_)?metadata.(?:[\w\d\.\-]+))~',
            'ctx._source.$1',
            $expression
        );
    }
}
