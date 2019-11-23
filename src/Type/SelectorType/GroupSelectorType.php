<?php
namespace Povs\ListerBundle\Type\SelectorType;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class GroupSelectorType extends AbstractSelectorType
{
    private const DELIMITER = '|-|';

    /**
     * @inheritDoc
     */
    protected function getStatement(string $path): string
    {
        return sprintf('GROUP_CONCAT(%s SEPARATOR \'%s\')', $path, self::DELIMITER);
    }

    /**
     * @inheritDoc
     */
    protected function processValue($value): array
    {
        if (!$value) {
            return [];
        }

        return explode(self::DELIMITER, $value);
    }
}