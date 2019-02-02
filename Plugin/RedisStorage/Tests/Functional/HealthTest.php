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

namespace Apisearch\Plugin\RedisStorage\Tests\Functional;

use Apisearch\Plugin\RedisStorage\RedisStoragePluginBundle;
use Apisearch\Server\Tests\Functional\HttpFunctionalTest;

/**
 * Class HealthTest.
 */
class HealthTest extends HttpFunctionalTest
{
    /**
     * Decorate bundles.
     *
     * @param array $bundles
     *
     * @return array
     */
    protected static function decorateBundles(array $bundles): array
    {
        $bundles[] = RedisStoragePluginBundle::class;

        return $bundles;
    }

    /**
     * Save events.
     *
     * @return bool
     */
    protected static function saveEvents(): bool
    {
        return false;
    }

    /**
     * Test if health check has redis.
     */
    public function testCheckHealth()
    {
        $client = $this->createClient();
        $testRoute = static::get('router')->generate('search_server_api_check_health', [
            'token' => static::$godToken,
        ]);

        $client->request(
            'get',
            $testRoute
        );

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['status']['redis']);
    }
}
