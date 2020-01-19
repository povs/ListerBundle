<?php

namespace Povs\ListerBundle\Factory;

use Doctrine\ORM\QueryBuilder;
use Povs\ListerBundle\Service\ConfigurationResolver;
use Povs\ListerBundle\Service\Paginator;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class PaginatorFactory
{
    /**
     * @var ConfigurationResolver
     */
    private $configuration;

    /**
     * PaginatorFactory constructor.
     *
     * @param ConfigurationResolver $configuration
     */
    public function __construct(ConfigurationResolver $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param QueryBuilder $queryBuilder
     *
     * @return Paginator
     */
    public function createPaginator(QueryBuilder $queryBuilder): Paginator
    {
        return new Paginator(
            $queryBuilder,
            $this->configuration->getAlias(),
            $this->configuration->getIdentifier()
        );
    }
}
