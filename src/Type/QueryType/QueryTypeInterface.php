<?php
namespace Povs\ListerBundle\Type\QueryType;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
interface QueryTypeInterface
{
    /**
     * Filters query by filter field.
     *
     * @param QueryBuilder $queryBuilder
     * @param array        $paths        ORM field paths
     * @param string       $identifier   unique id to bind parameters
     * @param mixed        $value        input value from form. Can not be null.
     */
    public function filter(QueryBuilder $queryBuilder, array $paths, string $identifier, $value): void;

    /**
     * Configures query type options that are passed building filter
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void;

    /**
     * Sets options that are passed building filter
     *
     * @param array $options
     */
    public function setOptions(array $options): void;

    /**
     * @return bool
     */
    public function hasAggregation(): bool;
}