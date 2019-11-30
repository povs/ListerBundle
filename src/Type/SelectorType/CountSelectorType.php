<?php
namespace Povs\ListerBundle\Type\SelectorType;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class CountSelectorType extends BasicSelectorType
{
    /**
     * @inheritDoc
     */
    protected function getStatement(string $path): string
    {
        return sprintf('count(%s)', $path);
    }

    /**
     * @inheritDoc
     */
    protected function processValue($value): int
    {
        if (null === $value) {
            return 0;
        }

        return (int) $value;
    }

    public function hasAggregation(): bool
    {
        return true;
    }
}