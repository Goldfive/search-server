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

namespace Apisearch\Server\Domain\CommandHandler;

use Apisearch\Server\Domain\Command\DeleteItems;
use Apisearch\Server\Domain\Event\DomainEventWithRepositoryReference;
use Apisearch\Server\Domain\Event\ItemsWereDeleted;
use Apisearch\Server\Domain\WithRepositoryAndEventPublisher;

/**
 * Class DeleteItemsHandler.
 */
class DeleteItemsHandler extends WithRepositoryAndEventPublisher
{
    /**
     * Reset the delete.
     *
     * @param DeleteItems $deleteItems
     */
    public function handle(DeleteItems $deleteItems)
    {
        $repositoryReference = $deleteItems->getRepositoryReference();
        $itemsUUID = $deleteItems->getItemsUUID();

        $this
            ->repository
            ->deleteItems(
                $repositoryReference,
                $itemsUUID
            );

        $this
            ->eventPublisher
            ->publish(new DomainEventWithRepositoryReference(
                $repositoryReference,
                new ItemsWereDeleted($itemsUUID)
            ));
    }
}
