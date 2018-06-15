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

namespace Apisearch\Server\Socket;

use Clue\React\Redis\Client as ReactRedisClient;
use Clue\React\Redis\Factory as ReactRedisFactory;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ServerFactory.
 */
class ServerFactory
{
    /**
     * Run at port.
     *
     * @param App    $app
     * @param string $port
     * @param string $queueName
     *
     * @return IoServer
     */
    public static function create(
        App $app,
        string $port,
        string $queueName
    ) {
        $server = IoServer::factory(
            new HttpServer(
                new WsServer($app)
            ),
            $port
        );

        $appConfig = Yaml::parse(file_get_contents(__DIR__.'/../app.yml'))['config'];
        $redisFactory = new ReactRedisFactory($server->loop);
        $redisFactory
            ->createClient('127.0.0.1:'.$appConfig['rs_queue']['server']['redis']['port'])
            ->then(function (ReactRedisClient $client) use ($app, $queueName) {
                $client->subscribe($queueName);
                $client->on('message', function ($channel, $payload) use ($app) {
                    var_dump($payload);
                    $app->write(json_decode($payload, true));
                });
            });

        return $server;
    }
}
