<?php
namespace Povs\ListerBundle\Type\QueryType;

use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class HavingQueryType extends AbstractQueryType
{
    public const COUNT = 'count';
    public const SUM = 'sum';
    public const AVG = 'avg';
    public const MIN = 'min';
    public const MAX = 'max';

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(['type', 'function']);
        $resolver->setDefault('type',Comparison::EQ);
        $resolver->setDefault('function',self::COUNT);
        $resolver->setAllowedValues('type', [
            Comparison::EQ, Comparison::GT, Comparison::GTE, Comparison::LT, Comparison::LTE, Comparison::NEQ
        ]);
        $resolver->setAllowedValues('function', [
            self::COUNT, self::SUM, self::AVG, self::MIN, self::MAX
        ]);
    }

    /**
     * @inheritDoc
     */
    public function filter(QueryBuilder $queryBuilder, array $paths, string $identifier, $value): void
    {
        $identifier = $this->parseIdentifier($identifier);
        $clause = sprintf('%s(%s) %s %s',
            $this->getOption('function'),
            $paths[0],
            $this->getOption('type'),
            $identifier
        );
        $queryBuilder->andHaving($clause)
            ->setParameter($identifier, $value);
    }

    /**
     * @inheritDoc
     */
    public function hasAggregation(): bool
    {
        return true;
    }
}