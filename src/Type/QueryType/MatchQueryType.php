<?php
namespace Povs\ListerBundle\Type\QueryType;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class MatchQueryType extends AbstractQueryType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(['relevance', 'boolean', 'expand']);
        $resolver->setDefaults([
            'relevance' => 0,
            'boolean' => false,
            'expand' => false
        ]);

        $resolver->setAllowedTypes('relevance', ['int', 'double']);
        $resolver->setAllowedTypes('boolean', 'bool');
        $resolver->setAllowedTypes('expand', 'bool');
    }

    /**
     * @inheritDoc
     */
    public function filter(QueryBuilder $queryBuilder, string $identifier, $value): void
    {
        $identifier = $this->parseIdentifier($identifier);
        $clause = sprintf('MATCH (%s) HAVING (%s%s%s)',
            implode(',', $this->paths),
            $identifier,
            $this->getOption('boolean') ? ' boolean' : '',
            $this->getOption('expand') ? ' expand' : ''
        );

        $queryBuilder->andWhere($clause)
            ->setParameter($identifier, $value);
    }
}