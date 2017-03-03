<?php

/*
 * This file is part of the SearchBundle for Symfony2.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace Mmoreram\SearchBundle\Query;

/**
 * Class PriceRange.
 */
class PriceRange
{
    /**
     * @var int
     *
     * free
     */
    const FREE = 0;

    /**
     * @var int
     *
     * Infinite
     */
    const INFINITE = -1;
}