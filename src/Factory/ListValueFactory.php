<?php
namespace Povs\ListerBundle\Factory;

use Povs\ListerBundle\Declaration\ListValueInterface;
use Povs\ListerBundle\Mapper\FilterMapper;
use Povs\ListerBundle\Mapper\ListMapper;
use Povs\ListerBundle\Service\ListValue;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListValueFactory
{
    /**
     * @param ListMapper   $listMapper
     * @param FilterMapper $filterMapper
     *
     * @return ListValueInterface
     */
    public function createListValue(ListMapper $listMapper, FilterMapper $filterMapper): ListValueInterface
    {
        return new ListValue($listMapper, $filterMapper);
    }
}