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

namespace Apisearch\Server\Domain\Query;

use Apisearch\Query\Query as SearchQuery;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\CommandWithRepositoryReferenceAndToken;
use Apisearch\Server\Domain\IndexRequiredCommand;
use Apisearch\Server\Domain\LoggableCommand;
use Apisearch\Token\Token;

/**
 * Class Query.
 */
class Query extends CommandWithRepositoryReferenceAndToken implements LoggableCommand, IndexRequiredCommand
{
    /**
     * @var SearchQuery
     *
     * Query
     */
    private $query;

    /**
     * DeleteCommand constructor.
     *
     * @param RepositoryReference $repositoryReference
     * @param Token               $token
     * @param SearchQuery         $query
     */
    public function __construct(
        RepositoryReference $repositoryReference,
        Token              $token,
        SearchQuery $query
    ) {
        parent::__construct(
            $repositoryReference,
            $token
        );

        $this->query = $query;
    }

    /**
     * Get Query.
     *
     * @return SearchQuery
     */
    public function getQuery(): SearchQuery
    {
        return $this->query;
    }
}
