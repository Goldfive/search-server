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

namespace Apisearch\Server\Tests\Unit\Domain\Command;

use Apisearch\Config\Config;
use Apisearch\Model\AppUUID;
use Apisearch\Model\IndexUUID;
use Apisearch\Model\Token;
use Apisearch\Model\TokenUUID;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\Command\ConfigureIndex;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfigureIndexTest.
 */
class ConfigureIndexTest extends TestCase
{
    /**
     * Test interaction asynchronous implementation.
     */
    public function testAsynchronous()
    {
        $appUUID = AppUUID::createById('main');
        $indexUUID = IndexUUID::createById('default');
        $repositoryReference = RepositoryReference::create(
            $appUUID,
            $indexUUID
        );
        $token = new Token(TokenUUID::createById('9999'), $appUUID);
        $configuration = new Config();

        $configureIndex = new ConfigureIndex(
            $repositoryReference,
            $token,
            $indexUUID,
            $configuration
        );

        $builtConfigureIndex = ConfigureIndex::fromArray($configureIndex->toArray());
        $this->assertEquals(
            $configureIndex,
            $builtConfigureIndex
        );

        $this->assertEquals(
            $configuration,
            $builtConfigureIndex->getConfig()
        );

        $this->assertEquals(
            $repositoryReference,
            $builtConfigureIndex->getRepositoryReference()
        );

        $this->assertEquals(
            $token,
            $builtConfigureIndex->getToken()
        );
    }
}
