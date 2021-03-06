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

namespace Apisearch\Plugin\RedisQueue\Tests\Functional;

use Apisearch\Server\Tests\Functional\Consumer\ConsumerManagerTest as BaseTest;

/**
 * Class ConsumerManagerTest.
 */
class ConsumerManagerTest extends BaseTest
{
    use RedisQueueTestTrait;

    /**
     * Decorate configuration.
     *
     * @param array $configuration
     *
     * @return array
     */
    protected static function decorateConfiguration(array $configuration): array
    {
        $configuration = parent::decorateConfiguration($configuration);
        $configuration['imports'][] = ['resource' => '@RedisQueuePluginBundle/Resources/test/domain.yml'];

        return $configuration;
    }
}
