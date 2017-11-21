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

namespace Puntmig\Search\Server\Domain\Event;

use Puntmig\Search\Model\Item;

/**
 * Class ItemsWereIndexed.
 */
class ItemsWereIndexed extends DomainEvent
{
    /**
     * @var Item[]
     *
     * Items
     */
    private $items;

    /**
     * ItemsWasIndexed constructor.
     *
     * @param Item[] $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
        $this->setNow();
    }

    /**
     * Payload to array.
     *
     * @return array
     */
    public function payloadToArray(): array
    {
        return [
            'items' => array_values(
                array_map(function (Item $item) {
                    return $item->toArray();
                }, $this->items)
            ),
        ];
    }

    /**
     * To payload.
     *
     * @param string $data
     *
     * @return array
     */
    public static function stringToPayload(string $data): array
    {
        return [
            array_map(function (array $item) {
                return Item::createFromArray($item);
            }, json_decode($data, true)['items']),
        ];
    }
}
