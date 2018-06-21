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

namespace Apisearch\Server;

use Apisearch\ApisearchBundle;
use Apisearch\Server\DependencyInjection\ApisearchServerExtension;
use Apisearch\Server\DependencyInjection\CompilerPass;
use League\Tactician\Bundle\TacticianBundle;
use Mmoreram\BaseBundle\BaseBundle;
use RSQueueBundle\RSQueueBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class PuntmigSearchServerBundle.
 */
class ApisearchServerBundle extends BaseBundle
{
    /**
     * @var KernelInterface
     *
     * Kernel
     */
    protected $kernel;

    /**
     * ApisearchServerBundle constructor.
     *
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Returns the bundle's container extension.
     *
     * @return ExtensionInterface|null The container extension
     *
     * @throws \LogicException
     */
    public function getContainerExtension()
    {
        return new ApisearchServerExtension();
    }

    /**
     * Return all bundle dependencies.
     *
     * Values can be a simple bundle namespace or its instance
     *
     * @return array
     */
    public static function getBundleDependencies(KernelInterface $kernel): array
    {
        return [
            ApisearchBundle::class,
            FrameworkBundle::class,
            MonologBundle::class,
            BaseBundle::class,
            RSQueueBundle::class,
            ApisearchBundle::class,
            new TacticianBundle(),
        ];
    }

    /**
     * Return a CompilerPass instance array.
     *
     * @return CompilerPassInterface[]
     */
    public function getCompilerPasses(): array
    {
        return [
            new CompilerPass\ItemRepositoriesCompilerPass(),
            new CompilerPass\EventRepositoriesCompilerPass(),
            new CompilerPass\LogRepositoriesCompilerPass(),
            new CompilerPass\ElasticaConfigPathCompilerPass(),
            new CompilerPass\DomainEventsMiddlewareCompilerPass(),
            new CompilerPass\LogsMiddlewareCompilerPass(),
            new CompilerPass\AppRepositoriesCompilerPass(),
            new CompilerPass\UserRepositoriesCompilerPass(),
            new CompilerPass\CommandBusCompilerPass(),
            new CompilerPass\PluginsMiddlewareCompilerPass(),
            new CompilerPass\EnabledPluginsMiddlewareCompilerPass($this->kernel),
            new CompilerPass\TokenRepositoryCompilerPass(),
            new CompilerPass\DomainEventsSubscribersCompilerPass(),
        ];
    }
}
