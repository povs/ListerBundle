<?php
namespace Povs\ListerBundle\Type\QueryType;

use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class LikeQueryType extends AbstractQueryType
{
    public const TYPE_DEFAULT = 'default';
    public const TYPE_WILDCARD_START = 'wildcard_start';
    public const TYPE_WILDCARD_END = 'wildcard_end';
    public const TYPE_WILDCARD = 'wildcard';
    
    private static $wildCardMap = [
        self::TYPE_DEFAULT => '%s',
        self::TYPE_WILDCARD_START => '%%%s',
        self::TYPE_WILDCARD_END => '%s%%',
        self::TYPE_WILDCARD => '%%%s%%'
    ];

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined('type');
        $resolver->setDefault('type', self::TYPE_DEFAULT);
        $resolver->setAllowedValues('type', [
            self::TYPE_DEFAULT, self::TYPE_WILDCARD_START, self::TYPE_WILDCARD_END, self::TYPE_WILDCARD
        ]);
    }

    /**
     * @inheritDoc
     */
    public function filter(QueryBuilder $queryBuilder, string $identifier, $value): void
    {
        $identifier = $this->parseIdentifier($identifier);
        $queryBuilder->andWhere(new Comparison($this->path, 'LIKE', $identifier))
            ->setParameter($identifier, sprintf(self::$wildCardMap[$this->getOption('type')], $value));
    }
}