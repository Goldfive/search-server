<?php

/*
 * This file is part of the Search Server Bundle.
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

namespace Puntmig\Search\Server\Tests\Functional\Repository;

use Puntmig\Search\Model\Coordinate;
use Puntmig\Search\Query\Query;
use Puntmig\Search\Query\SortBy;

/**
 * Class SortTest.
 */
trait SortTest
{
    /**
     * Test sort by indexable metadata integer asc.
     */
    public function testSortByIndexableMetadataIntegerAsc()
    {
        $result = $this->query(Query::createMatchAll()->sortBy(['indexed_metadata.simple_int' => 'asc']));
        $this->assertResults(
            $result,
            ['5', '3', '2', '1', '4']
        );
    }

    /**
     * Test sort by indexable metadata integer desc.
     */
    public function testSortByIndexableMetadataIntegerDesc()
    {
        $result = $this->query(Query::createMatchAll()->sortBy(['indexed_metadata.simple_int' => 'desc']));
        $this->assertResults(
            $result,
            ['4', '1', '2', '3', '5']
        );
    }

    /**
     * Test sort by indexable metadata string asc.
     */
    public function testSortByIndexableMetadataStringAsc()
    {
        $result = $this->query(Query::createMatchAll()->sortBy(['indexed_metadata.simple_string' => 'asc']));
        $this->assertResults(
            $result,
            ['5', '2', '3', '4', '1']
        );
    }

    /**
     * Test sort by indexable metadata string desc.
     */
    public function testSortByIndexableMetadataStringDesc()
    {
        $result = $this->query(Query::createMatchAll()->sortBy(['indexed_metadata.simple_string' => 'desc']));
        $this->assertResults(
            $result,
            ['1', '4', '3', '2', '5']
        );
    }

    /**
     * Test sort by location.
     */
    public function testSortByLocationKmAsc()
    {
        $result = $this->query(Query::createLocated(new Coordinate(45.0, 45.0), '')->sortBy(SortBy::LOCATION_KM_ASC));
        $this->assertResults(
            $result,
            ['3', '4', '2', '1', '5']
        );

        $items = $result->getItems();
        $this->assertTrue($items[0]->getDistance() < 558);
        $this->assertTrue($items[0]->getDistance() > 554);
    }

    /**
     * Test sort by location.
     */
    public function testSortByLocationKmDesc()
    {
        $result = $this->query(Query::createLocated(new Coordinate(45.0, 45.0), '')->sortBy(SortBy::LOCATION_MI_ASC));
        $this->assertResults(
            $result,
            ['3', '4', '2', '1', '5']
        );

        $items = $result->getItems();
        $this->assertTrue($items[0]->getDistance() < 346);
        $this->assertTrue($items[0]->getDistance() > 344);
    }

    /**
     * Test random sort.
     */
    public function testRandomSort()
    {
        $iterations = 10;
        $id = $this->generateFirstResultRandomSort();
        $sameIdTimes = 0;
        for ($i = 0; $i < $iterations; ++$i) {
            if ($id === $this->generateFirstResultRandomSort()) {
                ++$sameIdTimes;
            }
        }

        if ($sameIdTimes === $iterations) {
            $this->fail('Random sort is not working...');
        }
    }

    /**
     * Return first item from random search.
     */
    private function generateFirstResultRandomSort()
    {
        return $this->query(Query::createMatchAll()->sortBy(SortBy::RANDOM))->getFirstItem()->getId();
    }
}
