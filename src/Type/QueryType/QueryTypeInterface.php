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
     * @param array $paths array of ORM field paths
     */
    public function setPaths(array $paths): void;

    /**
     * @param string $path parsed single path to the field.
     *                     if multiple paths are passed while building filter field
     *                     paths will be a single string with delimiter as passed by option
     *                     merged with SQL CONCAT function
     */
    public function setPath(string $path): void;

    /**
     * Filters query by filter field.
     *
     * @param QueryBuilder $queryBuilder
     * @param string       $identifier unique id to bind parameters
     * @param mixed        $value      input value from form. Can not be null.
     */
    public function filter(QueryBuilder $queryBuilder, string $identifier, $value): void;

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
}