<?php

namespace Povs\ListerBundle\Declaration;

use Doctrine\ORM\QueryBuilder;
use Povs\ListerBundle\Mapper\FilterMapper;
use Povs\ListerBundle\Mapper\JoinMapper;
use Povs\ListerBundle\Mapper\ListMapper;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
interface ListInterface
{
    /**
     * Parameters that are passed to lister::buildList method
     *
     * @param array $parameters
     */
    public function setParameters(array $parameters): void;

    /**
     * Configures list - list_config configuration
     *
     * @return array list configuration
     */
    public function configure(): array;

    /**
     * Builds fields for list field type
     *
     * @param ListMapper $listMapper
     */
    public function buildListFields(ListMapper $listMapper): void;

    /**
     * Builds filter fields
     *
     * @param FilterMapper $filterMapper
     */
    public function buildFilterFields(FilterMapper $filterMapper): void;

    /**
     * Builds ORM joins
     *
     * @param JoinMapper         $joinMapper
     * @param ListValueInterface $value
     */
    public function buildJoinFields(JoinMapper $joinMapper, ListValueInterface $value): void;

    /**
     * For custom query configuration.
     *
     * @param QueryBuilder       $queryBuilder
     * @param ListValueInterface $value
     */
    public function configureQuery(QueryBuilder $queryBuilder, ListValueInterface $value): void;

    /**
     * @return string
     */
    public function getDataClass(): string;
}
