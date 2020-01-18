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
        if (($position = $field->getOption(ListField::OPTION_POSITION)) && $this->insertBefore($field, $position)) {
            return;
        }

        $this->fields->offsetSet($field->getId(), $field);
    }

    /**
     * @param AbstractField $field    Field to insert
     * @param string        $position Field id. New field will be inserted before this field.
     *
     * @return bool whether field was inserted.
     */
    private function insertBefore(AbstractField $field, string $position): bool
    {
        $fields = $this->fields->toArray();
        $offset = array_search($position, array_keys($fields), true);

        if (false === $offset) {
            return false;
        }

        $newFields = array_merge(
            array_slice($fields, 0, (int) $offset, true),
            [$field->getId() => $field],
            array_slice($fields, (int) $offset, null, true)
        );
        $this->fields = new ArrayCollection($newFields);

        return true;
    }
}
