<?php
namespace Povs\ListerBundle\Type\SelectorType;

use Doctrine\ORM\QueryBuilder;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class BasicSelectorType implements SelectorTypeInterface
{
    /**
     * @var array
     */
    protected $fieldsCountMap = [];

    /**
     * @inheritDoc
     */
    public function apply(QueryBuilder $queryBuilder, array $paths, string $id): void
    {
        $this->fieldsCountMap[$id] = count($paths);

        foreach ($paths as $key => $path) {
            $statement = $this->getStatement($path);
            $queryBuilder->addSelect(sprintf('%s as %s', $statement, $this->getAlias($id, $key)));
        }
    }

    /**
     * @inheritDoc
     */
    public function getValue(array $data, string $id)
    {
        $fieldData = [];

        for ($i = 0; $i < $this->fieldsCountMap[$id]; $i++) {
            $value = $this->processValue($data[$this->getAlias($id, $i)]);
            $fieldData[] = $value;
        }

        if (count($fieldData) === 1) {
            $fieldData = $fieldData[0];
        }

        return $fieldData;
    }

    /**
     * @inheritDoc
     */
    public function getSortPath(string $id): string
    {
        return $this->getAlias($id, 0);
    }

    /**
     * @return bool
     */
    public function hasAggregation(): bool
    {
        return false;
    }

    /**
     * @param string $id
     * @param int    $key
     *
     * @return string
     */
    protected function getAlias(string $id, int $key): string
    {
        return sprintf('%s_field_%s', $id, $key);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    protected function getStatement(string $path): string
    {
        return $path;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    protected function processValue($value)
    {
        return $value;
    }
}