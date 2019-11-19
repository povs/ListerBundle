<?php
namespace Povs\ListerBundle\Definition;

use Doctrine\ORM\QueryBuilder;
use Povs\ListerBundle\Mapper\FilterMapper;
use Povs\ListerBundle\Mapper\JoinMapper;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
abstract class AbstractList implements ListInterface
{
    /**
     * @inheritDoc
     */
    public function setParameters(array $parameters): void
    {
    }

    /**
     * @inheritDoc
     */
    public function configure(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function buildFilterFields(FilterMapper $filterMapper): void
    {
    }

    /**
     * @inheritdoc
     */
    public function buildJoinFields(JoinMapper $joinMapper, ListValueInterface $value): void
    {
        $joinMapper->build();
    }

    /**
     * @inheritDoc
     */
    public function configureQuery(QueryBuilder $queryBuilder, ListValueInterface $value): void
    {
    }
}