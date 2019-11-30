<?php
namespace Povs\ListerBundle\Type\QueryType;

use Povs\ListerBundle\Mixin\OptionsTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
abstract class AbstractQueryType implements QueryTypeInterface
{
    use OptionsTrait;

    /**
     * @inheritDoc
     */
    public function setOptions(array $options): void
    {
        $this->initOptions($options);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
    }

    /**
     * @inheritDoc
     */
    public function hasAggregation(): bool
    {
        return false;
    }

    /**
     * @param string $identifier
     *
     * @return string
     */
    protected function parseIdentifier(string $identifier): string
    {
        return sprintf(':%s', $identifier);
    }
}