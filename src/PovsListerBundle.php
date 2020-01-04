<?php
namespace Povs\ListerBundle;

use Povs\ListerBundle\Declaration\ListInterface;
use Povs\ListerBundle\DependencyInjection\Compiler\ListCompilerPass;
use Povs\ListerBundle\Type\FieldType\FieldTypeInterface;
use Povs\ListerBundle\Type\FilterType\FilterTypeInterface;
use Povs\ListerBundle\Type\ListType\ListTypeInterface;
use Povs\ListerBundle\Type\QueryType\QueryTypeInterface;
use Povs\ListerBundle\Type\SelectorType\SelectorTypeInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class PovsListerBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(ListInterface::class)
            ->addTag('povs_lister.list');
        $container->registerForAutoconfiguration(QueryTypeInterface::class)
            ->addTag('povs_lister.query_type');
        $container->registerForAutoconfiguration(FieldTypeInterface::class)
            ->addTag('povs_lister.field_type');
        $container->registerForAutoconfiguration(ListTypeInterface::class)
            ->addTag('povs_lister.list_type');
        $container->registerForAutoconfiguration(FilterTypeInterface::class)
            ->addTag('povs_lister.filter_type');
        $container->registerForAutoconfiguration(SelectorTypeInterface::class)
            ->addTag('povs_lister.selector_type');

        $container->addCompilerPass(new ListCompilerPass());

        parent::build($container);
    }
}