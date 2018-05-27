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

namespace Apisearch\Server\Controller;

use Apisearch\Config\ImmutableConfig;
use Apisearch\Http\Http;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\Command\CreateIndex;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CreateIndexController.
 */
class CreateIndexController extends ControllerWithBus
{
    /**
     * Create an index.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createIndex(Request $request): JsonResponse
    {
        $query = $request->query;
        $requestBody = $request->request;
        $plainConfig = $requestBody->get(Http::CONFIG_FIELD, '{}');

        $this
            ->commandBus
            ->handle(new CreateIndex(
                RepositoryReference::create(
                    $query->get(Http::APP_ID_FIELD),
                    $query->get(Http::INDEX_FIELD)
                ),
                $query->get(Http::TOKEN_FIELD),
                ImmutableConfig::createFromArray(json_decode($plainConfig, true))
            ));

        return new JsonResponse('Index created', 200);
    }
}
