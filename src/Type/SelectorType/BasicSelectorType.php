<?php
namespace Povs\ListerBundle\Type\SelectorType;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class BasicSelectorType implements SelectorTypeInterface
{
    private const DELIMITER = '|-|';

    /**
     * @inheritDoc
     */
    public function getStatement(array $paths, ?string $delimiter = self::DELIMITER): string
    {
        if (count($paths) === 1) {
            $select = $paths[0];
        } else {
            $select = 'CONCAT(';
            $lastItem = count($paths) - 1;

            foreach ($paths as $key => $item) {
                $select .= $lastItem === $key
                    ? $item
                    : sprintf('%s,\'%s\',', $item, $delimiter);
            }

            $select .= ')';
        }

        return $select;
    }

    /**
     * @inheritDoc
     */
    public function getValue(?string $value)
    {
        if (!$value) {
            return null;
        }

        if (false !== strpos($value, self::DELIMITER)) {
            $value = explode(self::DELIMITER, $value);
        }

        return $value;
    }
}