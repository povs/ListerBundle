<?php
namespace Povs\ListerBundle\Type\SelectorType;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class BasicSelectorType extends AbstractSelectorType
{
    /**
     * @inheritDoc
     */
    protected function getStatement(string $path): string
    {
        return $path;
    }

    /**
     * @inheritDoc
     */
    protected function processValue($value)
    {
        return $value;
    }
}