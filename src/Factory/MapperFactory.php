<?php
namespace Povs\ListerBundle\Factory;

use Povs\ListerBundle\Definition\ListInterface;
use Povs\ListerBundle\Definition\ListValueInterface;
use Povs\ListerBundle\DependencyInjection\Locator\FieldTypeLocator;
use Povs\ListerBundle\DependencyInjection\Locator\FilterTypeLocator;
use Povs\ListerBundle\Mapper\FilterMapper;
use Povs\ListerBundle\Mapper\JoinMapper;
use Povs\ListerBundle\Mapper\ListMapper;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class MapperFactory
{
    /**
     * @var FieldTypeLocator
     */
    private $fieldTypeLocator;

    /**
     * @var FilterTypeLocator
     */
    private $filterTypeLocator;

    /**
     * MapperFactory constructor.
     *
     * @param FieldTypeLocator  $fieldTypeLocator
     * @param FilterTypeLocator $filterTypeLocator
     */
    public function __construct(FieldTypeLocator $fieldTypeLocator, FilterTypeLocator $filterTypeLocator)
    {
        $this->fieldTypeLocator = $fieldTypeLocator;
        $this->filterTypeLocator = $filterTypeLocator;
    }

    /**
     * @param ListInterface $list
     * @param string        $type
     *
     * @return ListMapper
     */
    public function createListMapper(ListInterface $list, string $type): ListMapper
    {
        $baseMapper = new ListMapper($this->fieldTypeLocator, $type, null);
        $list->buildListFields($baseMapper);
        $methodName = sprintf('build%sFields', strtoupper($type));

        if ($type === 'list' || !method_exists($list, $methodName)) {
            return $baseMapper;
        }

        $typeMapper = new ListMapper($this->fieldTypeLocator, $type, $baseMapper);
        $list->{$methodName}($typeMapper);

        return $typeMapper;
    }

    /**
     * @param ListInterface $list
     *
     * @return FilterMapper
     */
    public function createFilterMapper(ListInterface $list): FilterMapper
    {
        $filterMapper = new FilterMapper($this->filterTypeLocator);
        $list->buildFilterFields($filterMapper);

        return $filterMapper;
    }

    /**
     * @param ListInterface      $list
     * @param ListMapper         $listMapper
     * @param FilterMapper       $filterMapper
     * @param ListValueInterface $listValue
     *
     * @return JoinMapper
     */
    public function createJoinMapper(
        ListInterface $list,
        ListMapper $listMapper,
        FilterMapper $filterMapper,
        ListValueInterface $listValue
    ): JoinMapper {
        $joinMapper = new JoinMapper($listMapper, $filterMapper);
        $list->buildJoinFields($joinMapper, $listValue);

        return $joinMapper;
    }
}