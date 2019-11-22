<?php
namespace Povs\ListerBundle\Type\SelectorType;

use Povs\ListerBundle\Exception\ListException;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class CountSelectorType extends AbstractSelectorType
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
}