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

namespace Apisearch\Server\Tests\Functional\Repository;

/**
 * Class HealthTest.
 */
trait HealthTest
{
    /**
     * Test ping.
     */
    public function testPing()
    {
        $this->assertTrue($this->ping());
    }

    /**
     * Test ping.
     */
    public function testCheckHealth()
    {
        $this->assertTrue(in_array($this->checkHealth()['status'], ['yellow', 'green', 'red']));
    }
}