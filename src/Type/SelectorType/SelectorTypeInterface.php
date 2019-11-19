<?php
namespace Povs\ListerBundle\Type\SelectorType;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
interface SelectorTypeInterface
{
    /**
     * @param array $paths parsed dql paths (alias.property[])
     *
     * @return string sql select statement
     */
    public function getStatement(array $paths): string;

    /**
     * @param string|null $value
     *
     * @return mixed
     */
    public function getValue(?string $value);
}