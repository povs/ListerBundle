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
        self::WILDCARD_START => '%%%s',
        self::WILDCARD_END => '%s%%',
        self::WILDCARD => '%%%s%%'
    ];

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(['type', 'wildcard', 'delimiter']);
        $resolver->setDefaults([
            'type' => Comparison::EQ,
            'wildcard' => self::NO_WILDCARD,
            'delimiter' => ' '
        ]);
        $resolver->setAllowedTypes('delimiter', 'string');
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
    public function filter(QueryBuilder $queryBuilder, array $paths, string $identifier, $value): void
    {
        $identifier = $this->parseIdentifier($identifier);
        $comparison = new Comparison($this->getPath($paths), $this->getOption('type'), $identifier);
        $queryBuilder->andWhere($comparison)
            ->setParameter($identifier, $this->getParameter($value));
    }

    /**
     * @param array $paths
     *
     * @return string
     */
    private function getPath(array $paths): string
    {
        if (count($paths) === 1) {
            $select = $paths[0];
        } else {
            $select = 'CONCAT(';
            $lastItem = count($paths) - 1;

            foreach ($paths as $key => $item) {
                $select .= $lastItem === $key
                    ? $item
                    : sprintf('%s,\'%s\',', $item, $this->getOption('delimiter'));
            }

            $select .= ')';
        }

        return $select;
    }

    /**
     * @param string|object $value
     *
     * @return string|object
     */
    private function getParameter($value)
    {
        if (($wildcard = $this->getOption('wildcard')) === self::NO_WILDCARD) {
            return $value;
        }

        return sprintf(self::$wildCardMap[$wildcard], $value);
    }
}