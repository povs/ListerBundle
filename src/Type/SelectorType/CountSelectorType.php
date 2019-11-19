<?php
namespace Povs\ListerBundle\Type\SelectorType;

use Povs\ListerBundle\Exception\ListException;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class CountSelectorType implements SelectorTypeInterface
{
    /**
     * @inheritDoc
     */
    public function getStatement(array $paths): string
    {
        if (count($paths) > 1) {
            throw ListException::singlePathSelector(self::class);
        }

        return sprintf('count(%s)', $paths[0]);
    }

    /**
     * @inheritDoc
     */
    public function getValue(?string $value): int
    {
        if (null === $value) {
            return 0;
        }

        return (int) $value;
    }
}