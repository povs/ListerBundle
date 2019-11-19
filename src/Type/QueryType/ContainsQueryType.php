<?php
namespace Povs\ListerBundle\Type\QueryType;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ContainsQueryType extends AbstractQueryType
{
    /**
     * @inheritDoc
     */
    public function filter(QueryBuilder $queryBuilder, string $identifier, $value): void
    {
        $identifier = $this->parseIdentifier($identifier);
        $queryBuilder->andWhere(sprintf('%s IN %s', $this->path, $identifier))
            ->setParameter($identifier, $value);
    }
}