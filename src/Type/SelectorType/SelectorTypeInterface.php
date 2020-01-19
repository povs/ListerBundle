<?php

namespace Povs\ListerBundle\Type\SelectorType;

use Doctrine\ORM\QueryBuilder;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
interface SelectorTypeInterface
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param array        $paths        parsed dql paths (alias.property[])
     * @param string       $id           unique field id
     */
    public function apply(QueryBuilder $queryBuilder, array $paths, string $id): void;

    /**
     * @param array  $data row data
     * @param string $id   unique field id
     *
     * @return mixed
     */
    public function getValue(array $data, string $id);

    /**
     * @param string $id
     *
     * @return string
     */
    public function getSortPath(string $id): string;

    /**
     * @return bool
     */
    public function hasAggregation(): bool;
}
