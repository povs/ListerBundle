<?php

namespace Povs\ListerBundle\DependencyInjection;

use Povs\ListerBundle\Type\ListType\ArrayListType;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('povs_lister');
        $rootNode = $treeBuilder->root('povs_lister');

        $rootNode->children()
            ->scalarNode('default_type')
                ->defaultValue('list')
            ->end();

        $this->addTypesConfiguration($rootNode);
        $this->addListOptionsConfiguration($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    private function addTypesConfiguration(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
            ->arrayNode('types')
                ->useAttributeAsKey('name')
                ->prototype('scalar')->end()
                ->defaultValue([
                    'list' => ArrayListType::class,
                ])
                ->requiresAtLeastOneElement()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    private function addListOptionsConfiguration(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('list_config')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('identifier')->defaultValue('id')->cannotBeEmpty()->end()
                        ->scalarNode('alias')->defaultValue('l')->cannotBeEmpty()->end()
                        ->booleanNode('translate')->defaultValue(false)->end()
                        ->scalarNode('translation_domain')->defaultNull()->end()
                        ->arrayNode('form_configuration')
                            ->variablePrototype()->end()
                        ->end()
                        ->arrayNode('request')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('page')->defaultValue('page')->cannotBeEmpty()->end()
                                ->scalarNode('length')->defaultValue('length')->cannotBeEmpty()->end()
                                ->scalarNode('sort')->defaultValue('sort')->cannotBeEmpty()->end()
                                ->scalarNode('filter')->defaultValue(null)->end()
                            ->end()
                        ->end()
                        ->arrayNode('types')
                            ->arrayPrototype()->ignoreExtraKeys(false)->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}