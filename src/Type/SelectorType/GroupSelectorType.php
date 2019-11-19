<?php
namespace Povs\ListerBundle\Type\SelectorType;

use Povs\ListerBundle\Exception\ListException;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class GroupSelectorType implements SelectorTypeInterface
{
    private const DELIMITER = '|-|';

    /**
     * @inheritDoc
     */
    public function getStatement(array $paths): string
    {
        if (count($paths) > 1) {
            throw ListException::singlePathSelector(self::class);
        }

        $path = $paths[0];

        return sprintf('GROUP_CONCAT(%s SEPARATOR \'%s\')', $path, self::DELIMITER);
    }

    /**
     * @inheritDoc
     */
    public function getValue(?string $value): array
    {
        if (null === $value) {
            return [];
        }

        return explode(self::DELIMITER, $value);
    }
}