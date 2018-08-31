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

use Apisearch\Http\Http;
use Apisearch\Model\AppUUID;
use Apisearch\Model\IndexUUID;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\Query\CheckIndex;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CheckIndexController.
 */
class CheckIndexController extends ControllerWithBus
{
    /**
     * Create an index.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        $query = $request->query;

        $alive = $this
            ->commandBus
            ->handle(new CheckIndex(
                RepositoryReference::create(
                    AppUUID::createById($query->get(Http::APP_ID_FIELD, '')),
                    IndexUUID::createById($query->get(Http::INDEX_FIELD, ''))
                ),
                $query->get(Http::TOKEN_FIELD, ''),
                IndexUUID::createById($query->get(Http::INDEX_FIELD, ''))
            ));

        return true === $alive
            ? new Response('', Response::HTTP_OK)
            : new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
