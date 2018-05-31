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

use Apisearch\Http\Http;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\Command\DeleteEventsIndex;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DeleteEventsIndexController.
 */
class DeleteEventsIndexController extends ControllerWithBus
{
    /**
     * Delete the events index.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $query = $request->query;

        $this
            ->commandBus
            ->handle(new DeleteEventsIndex(
                RepositoryReference::create(
                    $query->get(Http::APP_ID_FIELD),
                    $query->get(Http::INDEX_FIELD)
                ),
                $query->get('token')
            ));

        return new JsonResponse('Events index deleted', 200);
    }
}
