<?php
namespace Povs\ListerBundle\Type\QueryType;

use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ComparisonQueryType extends AbstractQueryType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined('type');
        $resolver->setDefault('type',Comparison::EQ);
        $resolver->setAllowedValues('type', [
            Comparison::EQ, Comparison::GT, Comparison::GTE, Comparison::LT, Comparison::LTE, Comparison::NEQ
        ]);
    }

    /**
     * @inheritDoc
     */
    public function filter(QueryBuilder $queryBuilder, string $identifier, $value): void
    {
        $identifier = $this->parseIdentifier($identifier);
        $comparison = new Comparison($this->path, $this->getOption('type'), $identifier);
        $queryBuilder->andWhere($comparison)
            ->setParameter($identifier, $value);
    }
}