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
    public const NO_WILDCARD = 'no_wildcard';
    public const WILDCARD_START = 'wildcard_start';
    public const WILDCARD_END = 'wildcard_end';
    public const WILDCARD = 'wildcard';
    public const COMPARISON_LIKE = 'LIKE';

    private static $wildCardMap = [
        self::NO_WILDCARD => '%s',
        self::WILDCARD_START => '%%%s',
        self::WILDCARD_END => '%s%%',
        self::WILDCARD => '%%%s%%'
    ];

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(['type', 'wildcard']);
        $resolver->setDefaults([
            'type' => Comparison::EQ,
            'wildcard' => self::NO_WILDCARD
        ]);
        $resolver->setAllowedValues('type', [
            Comparison::EQ,
            Comparison::GT,
            Comparison::GTE,
            Comparison::LT,
            Comparison::LTE,
            Comparison::NEQ,
            self::COMPARISON_LIKE
        ]);
        $resolver->setAllowedValues('wildcard', [
            self::NO_WILDCARD,
            self::WILDCARD_START,
            self::WILDCARD_END,
            self::WILDCARD
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
            ->setParameter($identifier, sprintf(self::$wildCardMap[$this->getOption('wildcard')], $value));
    }
}