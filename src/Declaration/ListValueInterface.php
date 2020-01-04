<?php
namespace Povs\ListerBundle\Declaration;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
interface ListValueInterface
{
    /**
     * @param string $id
     *
     * @return bool
     */
    public function hasFilterField(string $id): bool;
    /**
     * @param string $id
     *
     * @return bool
     */
    public function hasListField(string $id): bool;

    /**
     * @param string $id
     *
     * @return mixed|null
     */
    public function getFilterValue(string $id);

    /**
     * @param string $id
     *
     * @return string|null DESC|ASC
     */
    public function getSortValue(string $id): ?string;
}