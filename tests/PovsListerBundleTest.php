<?php
namespace Povs\ListerBundle;

use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\Declaration\ListInterface;
use Povs\ListerBundle\DependencyInjection\Compiler\ListCompilerPass;
use Povs\ListerBundle\Type\FieldType\FieldTypeInterface;
use Povs\ListerBundle\Type\FilterType\FilterTypeInterface;
use Povs\ListerBundle\Type\ListType\ListTypeInterface;
use Povs\ListerBundle\Type\QueryType\QueryTypeInterface;
use Povs\ListerBundle\Type\SelectorType\SelectorTypeInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class PovsListerBundleTest extends TestCase
{
    private $bundle;
    private $container;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->bundle = new PovsListerBundle();
    }

    public function testBuildTagsAdded(): void
    {
        $tagged = [
            'povs_lister.list' => ListInterface::class,
            'povs_lister.query_type' => QueryTypeInterface::class,
            'povs_lister.field_type' => FieldTypeInterface::class,
            'povs_lister.list_type' => ListTypeInterface::class,
            'povs_lister.filter_type' => FilterTypeInterface::class,
            'povs_lister.selector_type' => SelectorTypeInterface::class,
        ];

        $this->bundle->build($this->container);
        $configuredInstanceOf = $this->container->getAutoconfiguredInstanceof();

        foreach ($tagged as $tag => $instanceOf) {
            $this->assertArrayHasKey($instanceOf, $configuredInstanceOf);
            $this->assertArrayHasKey($tag, $configuredInstanceOf[$instanceOf]->getTags());
        }
    }

    public function testBuildListCompilerPassAdded(): void
    {
        $this->bundle->build($this->container);
        $passes = $this->container->getCompiler()->getPassConfig()->getBeforeOptimizationPasses();
        $hasPass = false;

        foreach ($passes as $pass) {
            if ($pass instanceof ListCompilerPass) {
                $hasPass = true;
            }
        }

        $this->assertTrue($hasPass);
    }
}