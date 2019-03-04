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

namespace Apisearch\Server\Tests\Functional\Domain\Repository;

use Apisearch\Server\Tests\Functional\AsynchronousFunctionalTest;
use RuntimeException;

/**
 * Class AsynchronousCommandNoBundleTest.
 */
class AsynchronousCommandNoBundleTest extends AsynchronousFunctionalTest
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public static function setUpBeforeClass()
    {
        try {
            parent::setUpBeforeClass();
            self::fail('Kernel should fail because no queue plugin is instanced');
        } catch (RuntimeException $e) {
        }
    }

    /**
     * Do something.
     *
     * If the test is executed, means that the kernel failed. That would mean a
     * good scenario
     */
    public function testSomething()
    {
        $this->assertTrue(true);
    }
}
