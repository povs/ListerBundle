<?php

namespace Povs\ListerBundle\Type\ListType;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
abstract class AbstractListType implements ListTypeInterface
{
    /**
     * @var array $typeConfig
     */
    protected $config;

    /**
     * @inheritDoc
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function configureSettings(OptionsResolver $optionsResolver): void
    {
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
    }
}
