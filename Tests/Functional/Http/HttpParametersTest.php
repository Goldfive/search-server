<?php
/*
 * This file is part of the {Package name}.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

namespace Apisearch\Server\Tests\Functional\Http;


use Apisearch\Server\Tests\Functional\HttpFunctionalTest;

/**
 * Class HttpParametersTest
 */
class HttpParametersTest extends HttpFunctionalTest
{
    /**
     * Test mandatory app_id parameter
     *
     * @group test
     */
    public function testMandatoryAppId()
    {
        $client = $this->createClient();
        $testRoute = static::get('router')->generate('search_server_api_query', [
            'token' => 'aaaa',
            'app_id' => '1234',
        ]);

        $client->request(
            'get',
            $testRoute
        );
    }
}