<?php
namespace Povs\ListerBundle\Type\SelectorType;

use Doctrine\ORM\QueryBuilder;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class GroupSelectorType extends BasicSelectorType
{
    private const DELIMITER = '|-|';
    private const SECONDARY_DELIMITER = '|,|';

    /**
     * @inheritDoc
     */
    public function apply(QueryBuilder $queryBuilder, array $paths, string $id): void
    {
        $statement = sprintf('GROUP_CONCAT(%s SEPARATOR \'%s\')', $this->getPath($paths),self::DELIMITER);
        $queryBuilder->addSelect(sprintf('%s as %s', $statement, $this->getAlias($id, 0)));
    }

    /**
     * @inheritDoc
     */
    public function getValue(array $data, string $id): array
    {
        $value = $data[$this->getAlias($id, 0)];

        if (!$value) {
            return [];
        }

        $hasSub = strpos($value, self::SECONDARY_DELIMITER) !== false;
        $value = explode(self::DELIMITER, $value);
        $finalValue = [];

        if ($hasSub) {
            foreach ($value as $val) {
                $finalValue[] = explode(self::SECONDARY_DELIMITER, $val);
            }
        } else {
            $finalValue = $value;
        }

        return $finalValue;
    }

    /**
     * @param array $paths
     *
     * @return string
     */
    private function getPath(array $paths): string
    {
        if (count($paths) === 1) {
            return $paths[0];
        }

        return implode(sprintf(',\'%s\',', self::SECONDARY_DELIMITER), $paths);
    }
}