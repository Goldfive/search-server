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

namespace Apisearch\Server\Tests\Unit\Domain\Repository\AppRepository;

use Apisearch\Model\AppUUID;
use Apisearch\Model\IndexUUID;
use Apisearch\Model\Token;
use Apisearch\Model\TokenUUID;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\Repository\AppRepository\InMemoryTokenRepository;
use PHPUnit\Framework\TestCase;

/**
 * Class InMemoryTokenRepositoryTest.
 */
class InMemoryTokenRepositoryTest extends TestCase
{
    /**
     * Test add and remove token.
     */
    public function testAddRemoveToken()
    {
        $repository = new InMemoryTokenRepository();
        $appUUID = AppUUID::createById('yyy');
        $indexUUID = IndexUUID::createById('index');
        $repositoryReference = RepositoryReference::create(
            $appUUID,
            $indexUUID
        );
        $tokenUUID = TokenUUID::createById('xxx');
        $token = new Token($tokenUUID, $appUUID);
        $repository->addToken($repositoryReference, $token);
        $this->assertEquals(
            $token,
            $repository->getTokenByUUID(
                $appUUID,
                $tokenUUID
            )
        );
        $this->assertNull($repository->getTokenByUUID($appUUID, TokenUUID::createById('lll')));
        $repository->deleteToken($repositoryReference, $tokenUUID);
        $this->assertNull($repository->getTokenByUUID($appUUID, $tokenUUID));
    }

    /**
     * Test delete tokens.
     */
    public function testDeleteTokens()
    {
        $repository = new InMemoryTokenRepository();
        $appUUID = AppUUID::createById('yyy');
        $indexUUID = IndexUUID::createById('index');
        $mainRepositoryReference = RepositoryReference::create(
            $appUUID,
            $indexUUID
        );
        $tokenUUID = TokenUUID::createById('xxx');
        $token = new Token($tokenUUID, $appUUID);
        $repository->addToken($mainRepositoryReference, $token);
        $tokenUUID2 = TokenUUID::createById('xxx2');
        $token2 = new Token($tokenUUID2, $appUUID);
        $repository->addToken($mainRepositoryReference, $token2);

        $repositoryReference = RepositoryReference::create(
            AppUUID::createById('zzz'),
            $indexUUID
        );
        $tokenUUID3 = TokenUUID::createById('xxx3');
        $token3 = new Token($tokenUUID3, AppUUID::createById('zzz'));
        $repository->addToken($repositoryReference, $token3);

        $this->assertCount(2, $repository->getTokens($mainRepositoryReference));
        $repositoryReference = RepositoryReference::create(
            AppUUID::createById('zzz'),
            $indexUUID
        );
        $this->assertCount(1, $repository->getTokens($repositoryReference));
        $repositoryReference = RepositoryReference::create(
            AppUUID::createById('lol'),
            $indexUUID
        );
        $this->assertCount(0, $repository->getTokens($repositoryReference));

        $repository->deleteTokens($mainRepositoryReference);
        $this->assertCount(0, $repository->getTokens($mainRepositoryReference));
    }
}
