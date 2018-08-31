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

namespace Apisearch\Server\Controller;

use Apisearch\Exception\InvalidFormatException;
use Apisearch\Exception\InvalidTokenException;
use Apisearch\Http\Http;
use Apisearch\Model\AppUUID;
use Apisearch\Model\IndexUUID;
use Apisearch\Query\Query;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\Query\QueryEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class EventsController.
 */
class EventsController extends ControllerWithBus
{
    /**
     * Query events.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws InvalidTokenException
     */
    public function __invoke(Request $request): JsonResponse
    {
        $query = $request->query;
        $queryAsArray = $this->getRequestContentObject(
            $request,
            Http::QUERY_FIELD,
            InvalidFormatException::queryFormatNotValid($request->getContent()),
            []
        );

        $eventsAsArray = $this
            ->commandBus
            ->handle(new QueryEvents(
                RepositoryReference::create(
                    AppUUID::createById($query->get(Http::APP_ID_FIELD, '')),
                    IndexUUID::createById($query->get(Http::INDEX_FIELD, ''))
                ),
                $query->get(Http::TOKEN_FIELD, ''),
                Query::createFromArray($queryAsArray),
                $this->castToIntIfNotNull($query, Http::FROM_FIELD),
                $this->castToIntIfNotNull($query, Http::TO_FIELD)
            ))
            ->toArray();

        return new JsonResponse(
            $eventsAsArray,
            200,
            [
                'Access-Control-Allow-Origin' => '*',
            ]
        );
    }
}
