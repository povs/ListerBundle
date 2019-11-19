<?php
namespace Povs\ListerBundle\Type\FilterType;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
interface FilterTypeInterface
{
    /**
     * @return array default filter field options
     */
    public function getDefaultOptions(): array;
}