<?php

namespace Povs\ListerBundle\Service;

use Povs\ListerBundle\Declaration\ListValueInterface;
use Povs\ListerBundle\Mapper\FilterMapper;
use Povs\ListerBundle\Mapper\ListField;
use Povs\ListerBundle\Mapper\ListMapper;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListValue implements ListValueInterface
{
    /**
     * @var ListMapper
     */
    private $listMapper;

    /**
     * @var FilterMapper
     */
    private $filterMapper;

    /**
     * MapperFacade constructor.
     *
     * @param ListMapper   $listMapper
     * @param FilterMapper $filterMapper
     */
    public function __construct(ListMapper $listMapper, FilterMapper $filterMapper)
    {
        $this->listMapper = $listMapper;
        $this->filterMapper = $filterMapper;
    }

    /**
     * @inheritDoc
     */
    public function hasFilterField(string $id): bool
    {
        return $this->filterMapper->has($id);
    }

    /**
     * @inheritDoc
     */
    public function hasListField(string $id): bool
    {
        return $this->listMapper->has($id);
    }

    /**
     * @inheritDoc
     */
    public function getFilterValue(string $id)
    {
        return $this->filterMapper->getValue($id);
    }

    /**
     * @inheritDoc
     */
    public function getSortValue(string $id): ?string
    {
        $field = $this->listMapper->get($id);

        return $field->getOption(ListField::OPTION_SORT_VALUE);
    }
}
