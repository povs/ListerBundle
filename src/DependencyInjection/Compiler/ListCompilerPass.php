<?php

namespace Povs\ListerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListCompilerPass implements CompilerPassInterface
{
    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container): void
    {
        $this->buildLocator($container, 'povs_lister.list', '.povs_lister.locator.list');
        $this->buildLocator($container, 'povs_lister.query_type', '.povs_lister.locator.query_type');
        $this->buildLocator($container, 'povs_lister.field_type', '.povs_lister.locator.field_type');
        $this->buildLocator($container, 'povs_lister.list_type', '.povs_lister.locator.list_type');
        $this->buildLocator($container, 'povs_lister.filter_type', '.povs_lister.locator.filter_type');
        $this->buildLocator($container, 'povs_lister.selector_type', '.povs_lister.locator.selector_type');
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $tag       tag name of the services
     * @param string           $locator   fully qualified class name
     *
     * @throws InvalidArgumentException
     * @throws ServiceNotFoundException
     */
    private function buildLocator(ContainerBuilder $container, string $tag, string $locator): void
    {
        $ref = [];

        foreach ($container->findTaggedServiceIds($tag, true) as $id => $tags) {
            $ref[$id] = new ServiceClosureArgument(new Reference($id));
        }

        $locatorDef = $container->getDefinition($locator);
        $locatorDef->setArgument(0, $ref);
    }
}
