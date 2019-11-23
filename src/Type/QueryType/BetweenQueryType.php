<?php
namespace Povs\ListerBundle\Type\QueryType;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class BetweenQueryType extends AbstractQueryType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined('value_delimiter');
        $resolver->setDefault('value_delimiter', '-');
        $resolver->setAllowedTypes('value_delimiter', 'string');
    }

    /**
     * @inheritDoc
     */
    public function filter(QueryBuilder $queryBuilder, array $paths, string $identifier, $value): void
    {
        $expr = new Expr();
        $from = sprintf(':%s_from', $identifier);
        $to = sprintf(':%s_to', $identifier);

        $queryBuilder->andWhere($expr->between($paths[0], $from, $to))
            ->setParameter($from, $this->getValue($value, 0))
            ->setParameter($to, $this->getValue($value, 1));
    }

    /**
     * @param string|array $value
     * @param int          $key
     *
     * @return string|null
     */
    protected function getValue($value, int $key = 0): ?string
    {
        if (!is_array($value)) {
            $value = explode($this->getOption('value_delimiter'), $value);
        }

        if (!array_key_exists($key, $value)) {
            return null;
        }

        return $value[$key];
    }
}