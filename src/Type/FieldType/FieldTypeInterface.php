<?php
namespace Povs\ListerBundle\Type\FieldType;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
interface FieldTypeInterface
{
    /**
     * @param mixed  $value parsed field value
     * @param string $type  current list type (i.e. list, export, etc..)
     *
     * @return mixed
     */
    public function getValue($value, string $type);

    /**
     * @param string $type current list type
     *
     * @return array default field options
     */
    public function getDefaultOptions(string $type): array;
}