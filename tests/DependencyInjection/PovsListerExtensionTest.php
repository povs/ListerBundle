<?php

namespace Povs\ListerBundle\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Povs\ListerBundle\Type\ListType\CsvListType;
use Povs\ListerBundle\Type\ListType\ArrayListType;
use Povs\ListerBundle\Type\QueryType\BetweenQueryType;
use Povs\ListerBundle\Type\QueryType\ComparisonQueryType;
use Povs\ListerBundle\Type\QueryType\ContainsQueryType;
use Povs\ListerBundle\Type\SelectorType\BasicSelectorType;
use Povs\ListerBundle\Type\SelectorType\CountSelectorType;
use Povs\ListerBundle\Type\SelectorType\GroupSelectorType;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class PovsListerExtensionTest extends TestCase
{
    private $container;
    private $extension;

    public function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->extension = new PovsListerExtension();
    }

    public function testLoadServicesDefinition(): void
    {
        $services = [
            'povs.lister',
            '.povs_lister.list_manager',
            '.povs_lister.type_resolver',
            '.povs_lister.configuration_resolver',
            '.povs_lister.filter_builder',
            '.povs_lister.query_builder',
            '.povs_lister.request_handler',
            '.povs_lister.value_accessor',
            '.povs_lister.factory.mapper',
            '.povs_lister.factory.view',
            '.povs_lister.factory.paginator',
            '.povs_lister.factory.list_value',
            '.povs_lister.locator.list_type',
            '.povs_lister.locator.field_type',
            '.povs_lister.locator.list',
            '.povs_lister.locator.query_type',
            '.povs_lister.locator.filter_type',
            '.povs_lister.locator.selector_type',
            CsvListType::class,
            ArrayListType::class,
            BetweenQueryType::class,
            ComparisonQueryType::class,
            ContainsQueryType::class,
            BasicSelectorType::class,
            CountSelectorType::class,
            GroupSelectorType::class,
        ];

        $this->extension->load([], $this->container);

        foreach ($services as $service) {
            $this->assertTrue($this->container->hasDefinition($service), $service);
        }
    }

    public function testLoadConfiguration(): void
    {
        $this->extension->load(['foo' => 'bar'], $this->container);
        $definition = $this->container->getDefinition('.povs_lister.configuration_resolver');
        $argument = $definition->getArgument(0);
        $this->assertEquals(['foo' => 'bar'], $argument);
    }
}
