<?php
namespace Povs\ListerBundle\Mapper;

use Povs\ListerBundle\Exception\ListFieldException;
use Povs\ListerBundle\Mixin\OptionsTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Throwable;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
abstract class AbstractField
{
    use OptionsTrait;

    /**
     * @var string
     */
    protected $id;

    /**
     * AbstractField constructor.
     *
     * @param string $id
     * @param array  $options
     */
    public function __construct(string $id, array $options)
    {
        $this->id = str_replace('.', '_', $id);
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        try {
            $options = $resolver->resolve($options);
        } catch (Throwable $e) {
            throw ListFieldException::invalidFieldConfiguration($id, $e->getMessage());
        }

        $this->initOptions($options);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Normalizes paths by path and property options. If both are not set - default path is id.
     *
     * @param $id
     *
     * @return array paths
     */
    protected function normalizePaths(string $id): array
    {
        $paths = (array) $this->getOption(ListField::OPTION_PATH);
        $properties = (array) $this->getOption(ListField::OPTION_PROPERTY);

        if (!$paths) {
            $paths = [$id];
        }

        if ($properties) {
            if (count($paths) > 1) {
                throw ListFieldException::invalidPropertiesOption($id);
            }

            $path = $paths[0];
            $paths = [];

            foreach ($properties as $property) {
                $paths[] = sprintf('%s.%s', $path, $property);
            }
        }

        return $paths;
    }

    /**
     * @param OptionsResolver $resolver
     */
    abstract protected function configureOptions(OptionsResolver $resolver): void;
}