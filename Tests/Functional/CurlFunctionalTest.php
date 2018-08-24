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

namespace Apisearch\Server\Tests\Functional;

use Apisearch\Config\Config;
use Apisearch\Config\ImmutableConfig;
use Apisearch\Exception\ConnectionException;
use Apisearch\Http\Endpoints;
use Apisearch\Http\HttpResponsesToException;
use Apisearch\Model\Changes;
use Apisearch\Model\Index;
use Apisearch\Model\Item;
use Apisearch\Model\ItemUUID;
use Apisearch\Model\User;
use Apisearch\Query\Query as QueryModel;
use Apisearch\Result\Events;
use Apisearch\Result\Logs;
use Apisearch\Result\Result;
use Apisearch\Token\Token;
use Apisearch\Token\TokenUUID;

/**
 * Class CurlFunctionalTest.
 */
abstract class CurlFunctionalTest extends ApisearchServerBundleFunctionalTest
{
    use HttpResponsesToException;

    /**
     * Query using the bus.
     *
     * @param QueryModel $query
     * @param string     $appId
     * @param string     $index
     * @param Token      $token
     *
     * @return Result
     */
    public function query(
        QueryModel $query,
        string $appId = null,
        string $index = null,
        Token $token = null
    ): Result {
        $result = self::makeCurl(
            'v1-query',
            $appId,
            $index,
            $token,
            ['query' => $query->toArray()]
        );

        return Result::createFromArray($result['body']);
    }

    /**
     * Delete using the bus.
     *
     * @param ItemUUID[] $itemsUUID
     * @param string     $appId
     * @param string     $index
     * @param Token      $token
     */
    public function deleteItems(
        array $itemsUUID,
        string $appId = null,
        string $index = null,
        Token $token = null
    ) {
        self::makeCurl(
            'v1-items-delete',
            $appId,
            $index,
            $token,
            ['items' => array_map(function (ItemUUID $itemUUID) {
                return $itemUUID->toArray();
            }, $itemsUUID)]
        );
    }

    /**
     * Add items using the bus.
     *
     * @param Item[] $items
     * @param string $appId
     * @param string $index
     * @param Token  $token
     */
    public static function indexItems(
        array $items,
        ?string $appId = null,
        ?string $index = null,
        ?Token $token = null
    ) {
        self::makeCurl(
            'v1-items-index',
            $appId,
            $index,
            $token,
            ['items' => array_map(function (Item $item) {
                return $item->toArray();
            }, $items)]
        );
    }

    /**
     * Update using the bus.
     *
     * @param QueryModel $query
     * @param Changes    $changes
     * @param string     $appId
     * @param string     $index
     * @param Token      $token
     */
    public function updateItems(
        QueryModel $query,
        Changes $changes,
        string $appId = null,
        string $index = null,
        Token $token = null
    ) {
        self::makeCurl(
            'v1-items-update',
            $appId,
            $index,
            $token,
            [
                'query' => $query->toArray(),
                'changes' => $changes->toArray(),
            ]
        );
    }

    /**
     * Reset index using the bus.
     *
     * @param string $appId
     * @param string $index
     * @param Token  $token
     */
    public function resetIndex(
        string $appId = null,
        string $index = null,
        Token $token = null
    ) {
        self::makeCurl(
            'v1-index-reset',
            $appId,
            $index,
            $token
        );
    }

    /**
     * @param string|null $appId
     *
     * @return array|Index[]
     */
    public function getIndices(string $appId = null): array
    {
        $result = self::makeCurl(
            'v1-indices-get',
            $appId,
            null,
            null,
            []
        );

        $indices = [];
        $body = $result['body'];
        foreach ($body as $item) {
            $indices[] = Index::createFromArray($item);
        }

        return $indices;
    }

    /**
     * Create index using the bus.
     *
     * @param string          $appId
     * @param string          $index
     * @param Token           $token
     * @param ImmutableConfig $config
     */
    public static function createIndex(
        string $appId = null,
        string $index = null,
        Token $token = null,
        ImmutableConfig $config = null
    ) {
        self::makeCurl(
            'v1-index-create',
            $appId,
            $index,
            $token,
            is_null($config)
                ? []
                : ['config' => $config->toArray()]
        );
    }

    /**
     * Configure index using the bus.
     *
     * @param Config $config
     * @param string $appId
     * @param string $index
     * @param Token  $token
     */
    public function configureIndex(
        Config $config,
        string $appId = null,
        string $index = null,
        Token $token = null
    ) {
        // TODO: Implement configureIndex() method.
    }

    /**
     * Check index.
     *
     * @param string $appId
     * @param string $index
     * @param Token  $token
     *
     * @return bool
     */
    public function checkIndex(
        string $appId = null,
        string $index = null,
        Token $token = null
    ): bool {
        try {
            $result = self::makeCurl(
                'v1-index-check',
                $appId,
                $index,
                $token,
                []
            );
        } catch (ConnectionException $exception) {
            return false;
        }

        return '200' === $result['code'];
    }

    /**
     * Delete index using the bus.
     *
     * @param string $appId
     * @param string $index
     * @param Token  $token
     */
    public static function deleteIndex(
        string $appId = null,
        string $index = null,
        Token $token = null
    ) {
        self::makeCurl(
            'v1-index-delete',
            $appId,
            $index,
            $token
        );
    }

    /**
     * Add token.
     *
     * @param Token  $newToken
     * @param string $appId
     * @param Token  $token
     */
    public static function addToken(
        Token $newToken,
        string $appId = null,
        Token $token = null
    ) {
        self::makeCurl(
            'v1-token-add',
            $appId,
            null,
            $token,
            ['token' => $newToken->toArray()]
        );
    }

    /**
     * Delete token.
     *
     * @param TokenUUID $tokenUUID
     * @param string    $appId
     * @param Token     $token
     */
    public static function deleteToken(
        TokenUUID $tokenUUID,
        string $appId = null,
        Token $token = null
    ) {
        self::makeCurl(
            'v1-token-delete',
            $appId,
            null,
            $token,
            ['token' => $tokenUUID->toArray()]
        );
    }

    /**
     * Get tokens.
     *
     * @param string $appId
     * @param Token  $token
     *
     * @return Token[]
     */
    public static function getTokens(
        string $appId = null,
        Token $token = null
    ) {
        $result = self::makeCurl(
            'v1-tokens-get',
            $appId,
            null,
            $token
        );

        return array_map(function (array $tokenAsArray) {
            return Token::createFromArray($tokenAsArray);
        }, $result['body']);
    }

    /**
     * Delete token.
     *
     * @param string $appId
     * @param Token  $token
     */
    public static function deleteTokens(
        string $appId,
        Token $token = null
    ) {
        self::makeCurl(
            'v1-tokens-delete',
            $appId,
            null,
            $token
        );
    }

    /**
     * Query events.
     *
     * @param QueryModel $query
     * @param int|null   $from
     * @param int|null   $to
     * @param string     $appId
     * @param string     $index
     * @param Token      $token
     *
     * @return Events
     */
    public function queryEvents(
        QueryModel $query,
        ?int $from = null,
        ?int $to = null,
        string $appId = null,
        string $index = null,
        Token $token = null
    ): Events {
        $result = self::makeCurl(
            'v1-events',
            $appId,
            $index,
            $token,
            [
                'query' => $query->toArray(),
                'from' => $from,
                'to' => $to,
            ]
        );

        return Events::createFromArray($result['body']);
    }

    /**
     * Query logs.
     *
     * @param QueryModel $query
     * @param int|null   $from
     * @param int|null   $to
     * @param string     $appId
     * @param string     $index
     * @param Token      $token
     *
     * @return Logs
     */
    public function queryLogs(
        QueryModel $query,
        ?int $from = null,
        ?int $to = null,
        string $appId = null,
        string $index = null,
        Token $token = null
    ): Logs {
        $result = self::makeCurl(
            'v1-logs',
            $appId,
            $index,
            $token,
            [
                'query' => $query->toArray(),
                'from' => $from,
                'to' => $to,
            ]
        );

        return Logs::createFromArray($result['body']);
    }

    /**
     * Add interaction.
     *
     * @param string $userId
     * @param string $itemUUIDComposed
     * @param int    $weight
     * @param string $appId
     * @param Token  $token
     */
    public function addInteraction(
        string $userId,
        string $itemUUIDComposed,
        int $weight,
        string $appId,
        Token $token
    ) {
        self::makeCurl(
            'v1-interactions',
            $appId,
            null,
            $token,
            [
                'user' => User::createFromArray(['id' => $userId]),
                'item_uuid' => ItemUUID::createByComposedUUID($itemUUIDComposed),
                'weight' => $weight,
            ]
        );
    }

    /**
     * Delete all interactions.
     *
     * @param string $appId
     * @param Token  $token
     */
    public static function deleteAllInteractions(
        string $appId,
        Token $token = null
    ) {
        self::makeCurl(
            'v1-interactions-delete',
            $appId,
            null,
            $token
        );
    }

    /**
     * Ping.
     *
     * @param Token $token
     *
     * @return bool
     */
    public function ping(Token $token = null): bool
    {
        $result = self::makeCurl(
            'v1-ping',
            null,
            null,
            $token
        );
    }

    /**
     * Check health.
     *
     * @param Token $token
     *
     * @return array
     */
    public function checkHealth(Token $token = null): array
    {
        $result = self::makeCurl(
            'v1-check-health',
            null,
            null,
            $token
        );
    }

    /**
     * Configure environment.
     */
    public static function configureEnvironment()
    {
        // Pass
    }

    /**
     * Clean environment.
     */
    public static function cleanEnvironment()
    {
        // Pass
    }

    /**
     * Make a curl execution.
     *
     * @param string       $routeName
     * @param null|string  $appId
     * @param null|string  $index
     * @param null|Token   $token
     * @param array|string $body
     *
     * @return array
     */
    protected static function makeCurl(
        string $routeName,
        ?string $appId,
        ?string $index,
        ?Token $token,
        $body = []
    ): array {
        $endpoint = Endpoints::all()[$routeName];
        $tmpFile = tempnam('/tmp', 'curl_tmp');
        $command = sprintf('curl -s -o %s -w "%%{http_code}" %s %s "http://localhost:'.static::HTTP_TEST_SERVICE_PORT.'%s?app_id=%s&index=%s&token=%s" -d\'%s\'',
            $tmpFile,
            (
                'head' === $endpoint['verb']
                    ? '--head'
                    : '-X'.$endpoint['verb']
            ),
            (
                empty($body)
                    ? ''
                    : '-H "Content-Type: application/json"'
            ),
            $endpoint['path'],
            $appId ?? self::$appId,
            $index ?? self::$index,
            $token
                ? $token->getTokenUUID()->composeUUID()
                : self::getParameterStatic('apisearch_server.god_token'),
            is_string($body)
                ? $body
                : json_encode($body)
        );

        $command = str_replace("-d'[]'", '', $command);

        $responseCode = exec($command);
        $result = [
            'code' => $responseCode,
            'body' => json_decode(file_get_contents($tmpFile), true),
        ];
        unlink($tmpFile);

        self::throwTransportableExceptionIfNeeded($result);

        return $result;
    }
}
