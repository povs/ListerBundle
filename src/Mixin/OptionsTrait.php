<?php

namespace Povs\ListerBundle\Mixin;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
trait OptionsTrait
{
    /**
     * @var ArrayCollection
     */
    private $options;

    /**
     * Must be called for using this trait.
     *
     * @param array $options
     */
    public function initOptions(array $options): void
    {
        $this->options = new ArrayCollection($options);
    }

    /**
     * @param string $option
     * @param mixed  $value
     *
     * @return $this
     */
    public function setOption(string $option, $value): self
    {
        $this->options->offsetSet($option, $value);

        return $this;
    }

    /**
     * @param string     $option
     * @param mixed|null $defaultValue
     *
     * @return mixed|null
     */
    public function getOption(string $option, $defaultValue = null)
    {
        if (!$this->options->containsKey($option)) {
            return $defaultValue;
        }

        return $this->options->offsetGet($option);
    }

    /**
     * @param string $option
     *
     * @return bool
     */
    public function hasOption(string $option): bool
    {
        return $this->options->containsKey($option);
    }
}
