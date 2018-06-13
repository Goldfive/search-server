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

namespace Apisearch\Server\Tests\Functional;

use Apisearch\App\AppRepository;
use Apisearch\Config\Config;
use Apisearch\Config\ImmutableConfig;
use Apisearch\Event\EventRepository;
use Apisearch\Log\LogRepository;
use Apisearch\Model\Changes;
use Apisearch\Model\Item;
use Apisearch\Model\ItemUUID;
use Apisearch\Model\User;
use Apisearch\Query\Query as QueryModel;
use Apisearch\Repository\Repository;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Result\Result;
use Apisearch\Token\Token;
use Apisearch\Token\TokenUUID;
use Apisearch\User\Interaction;
use Apisearch\User\UserRepository;

/**
 * Class HttpFunctionalTest.
 */
abstract class HttpFunctionalTest extends ServiceFunctionalTest
{
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
        return self::configureRepository($appId, $index, $token)
            ->query($query);
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
        $repository = self::configureRepository($appId, $index, $token);
        foreach ($itemsUUID as $itemUUID) {
            $repository->deleteItem($itemUUID);
        }
        $repository->flush();
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
        string $appId = null,
        string $index = null,
        Token $token = null
    ) {
        $repository = self::configureRepository($appId, $index, $token);
        foreach ($items as $item) {
            $repository->addItem($item);
        }
        $repository->flush();
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
        self::configureRepository($appId, $index, $token)
            ->updateItems($query, $changes);
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
        self::configureRepository($appId, $index, $token)
            ->resetIndex();
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
        self::configureRepository($appId, $index, $token)
            ->createIndex(
                $config ?? ImmutableConfig::createFromArray([])
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
        self::configureRepository($appId, $index, $token)
            ->configureIndex($config);
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
        return self::configureRepository($appId, $index, $token)
            ->checkIndex();
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
        self::configureRepository($appId, $index, $token)
            ->deleteIndex();
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
        self::configureAppRepository($appId, $token)
            ->addToken($newToken);
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
        self::configureAppRepository($appId, $token)
            ->deleteToken($tokenUUID);
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
        return self::configureAppRepository($appId, $token)
            ->getTokens();
    }

    /**
     * Delete all tokens.
     *
     * @param string $appId
     * @param Token  $token
     */
    public static function deleteTokens(
        string $appId = null,
        Token $token = null
    ) {
        return self::configureAppRepository($appId, $token)
            ->deleteTokens();
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
     */
    public function queryEvents(
        QueryModel $query,
        ?int $from = null,
        ?int $to = null,
        string $appId = null,
        string $index = null,
        Token $token = null
    ) {
        self::configureEventsRepository($appId, $index, $token)
            ->query($query, $from, $to);
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
     */
    public function queryLogs(
        QueryModel $query,
        ?int $from = null,
        ?int $to = null,
        string $appId = null,
        string $index = null,
        Token $token = null
    ) {
        self::configureLogsRepository($appId, $index, $token)
            ->query($query, $from, $to);
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
        self::configureUserRepository($appId, $token)
            ->addInteraction(new Interaction(
                new User($userId),
                ItemUUID::createByComposedUUID($itemUUIDComposed),
                $weight
            ));
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
        self::configureUserRepository($appId, $token)
            ->deleteAllInteractions();
    }

    /**
     * Configure repository.
     *
     * @param string $appId
     * @param string $index
     * @param Token  $token
     *
     * @return Repository
     */
    public static function configureRepository(
        string $appId = null,
        string $index = null,
        Token $token = null
    ): Repository {
        $index = $index ?? self::$index;
        $realIndex = empty($index) ? self::$index : $index;

        return self::configureAbstractRepository(
            rtrim('apisearch.repository_'.static::getRepositoryName().'.'.$realIndex, '.'),
            $appId,
            $index,
            $token
        );
    }

    /**
     * Configure app repository.
     *
     * @param string $appId
     * @param Token  $token
     *
     * @return AppRepository
     */
    private static function configureAppRepository(
        string $appId = null,
        Token $token = null
    ): AppRepository {
        return self::configureAbstractRepository(
            'apisearch.app_repository_'.static::getRepositoryName(),
            $appId,
            '*',
            $token
        );
    }

    /**
     * Configure events repository.
     *
     * @param string $appId
     * @param string $index
     * @param Token  $token
     *
     * @return EventRepository
     */
    private static function configureEventsRepository(
        string $appId = null,
        string $index = null,
        Token $token = null
    ): EventRepository {
        $index = $index ?? self::$index;
        $realIndex = empty($index) ? self::$index : $index;

        return self::configureAbstractRepository(
            rtrim('apisearch.event_repository_'.static::getRepositoryName().'.'.$realIndex, '.'),
            $appId,
            $index,
            $token
        );
    }

    /**
     * Configure logs repository.
     *
     * @param string $appId
     * @param string $index
     * @param Token  $token
     *
     * @return LogRepository
     */
    private static function configureLogsRepository(
        string $appId = null,
        string $index = null,
        Token $token = null
    ): LogRepository {
        $index = $index ?? self::$index;
        $realIndex = empty($index) ? self::$index : $index;

        return self::configureAbstractRepository(
            rtrim('apisearch.log_repository_'.static::getRepositoryName().'.'.$realIndex, '.'),
            $appId,
            $index,
            $token
        );
    }

    /**
     * Configure user repository.
     *
     * @param string $appId
     * @param Token  $token
     *
     * @return UserRepository
     */
    private static function configureUserRepository(
        string $appId = null,
        Token $token = null
    ): UserRepository {
        return self::configureAbstractRepository(
            'apisearch.user_repository_'.static::getRepositoryName(),
            $appId,
            '*',
            $token
        );
    }

    /**
     * Configure abstract repository.
     *
     * @param string $repositoryName
     * @param string $appId
     * @param string $index
     * @param Token  $token
     *
     * @return mixed
     */
    private static function configureAbstractRepository(
        string $repositoryName,
        string $appId = null,
        string $index = null,
        Token $token = null
    ) {
        $repository = self::getStatic($repositoryName);
        $repository->setCredentials(
            RepositoryReference::create(
                $appId ?? self::$appId,
                $index ?? self::$index
            ),
            self::getTokenId($token)
        );

        return $repository;
    }

    /**
     * Get token id.
     *
     * @param Token $token
     *
     * @return string
     */
    protected static function getTokenId(Token $token = null): string
    {
        return ($token instanceof Token)
                ? $token->getTokenUUID()->composeUUID()
                : self::getParameterStatic('apisearch_server.god_token');
    }

    /**
     * Get repository name.
     *
     * @return string
     */
    protected static function getRepositoryName(): string
    {
        return 'search_http';
    }
}
