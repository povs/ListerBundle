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
     * @var array
     */
    protected $paths;

    /**
     * @var string
     */
    protected $path;

    /**
     * @inheritDoc
     */
    public function setPaths(array $paths): void
    {
        $this->paths = $paths;
    }

    /**
     * @inheritDoc
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

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
     * @param string $identifier
     *
     * @return string
     */
    protected function parseIdentifier(string $identifier): string
    {
        return sprintf(':%s', $identifier);
    }
}