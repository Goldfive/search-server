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
use Apisearch\Server\Tests\Functional\Domain\Token\TokenTest;

/**
 * Class BasicTokensTest.
 */
class BasicTokensTest extends TokenTest
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
}
