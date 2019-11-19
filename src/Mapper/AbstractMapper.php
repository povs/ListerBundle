<?php
namespace Povs\ListerBundle\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use Povs\ListerBundle\Exception\ListFieldException;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
abstract class AbstractMapper
{
    /**
     * @var AbstractField[]|ArrayCollection
     */
    protected $fields = [];

    /**
     * AbstractMapper constructor.
     */
    public function __construct()
    {
        $this->fields = new ArrayCollection();
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return $this->fields->containsKey($id);
    }

    /**
     * @param string $id
     *
     * @return AbstractField
     */
    public function get(string $id): AbstractField
    {
        if (!$this->has($id)) {
            throw ListFieldException::fieldNotExists($id);
        }

        return $this->fields->offsetGet($id);
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function remove(string $id): self
    {
        if ($this->has($id)) {
            $this->fields->offsetUnset($id);
        }

        return $this;
    }

    /**
     * @return ArrayCollection|AbstractField[]
     */
    public function getFields(): ArrayCollection
    {
        return $this->fields;
    }

    /**
     * @param AbstractField $field
     */
    protected function addField(AbstractField $field): void
    {
        $this->fields->offsetSet($field->getId(), $field);
    }
}