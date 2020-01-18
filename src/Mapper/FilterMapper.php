<?php

namespace Povs\ListerBundle\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use Povs\ListerBundle\DependencyInjection\Locator\FilterTypeLocator;

/**
 * @property FilterField[] $fields
 * @method FilterField get(string $id)
 * @method FilterField[]|ArrayCollection getFields()
 *
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class FilterMapper extends AbstractMapper
{
    /**
     * @var FilterTypeLocator
     */
    private $filterTypeLocator;

    /**
     * FilterMapper constructor.
     *
     * @param FilterTypeLocator $filterTypeLocator
     */
    public function __construct(FilterTypeLocator $filterTypeLocator)
    {
        parent::__construct();
        $this->filterTypeLocator = $filterTypeLocator;
    }

    /**
     * @param string      $id
     * @param string|null $filterType filter type fully qualified name
     * @param array|null  $options
     *
     * @return $this
     */
    public function add(string $id, ?string $filterType = null, ?array $options = []): AbstractMapper
    {
        if (null !== $filterType) {
            $filterType = $this->filterTypeLocator->get($filterType);
            $options = array_merge($filterType->getDefaultOptions(), $options);
        }

        $field = new FilterField($id, $options, $filterType);
        $this->addField($field);

        return $this;
    }

    /**
     * @param string $id
     *
     * @return mixed|null
     */
    public function getValue(string $id)
    {
        return $this->get($id)->getValue();
    }

    /**
     * @param string $id
     * @param mixed  $value
     *
     * @return $this
     */
    public function setValue(string $id, $value): FilterMapper
    {
        $this->get($id)->setValue($value);

        return $this;
    }
}
