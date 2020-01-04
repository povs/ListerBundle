<?php
namespace Povs\ListerBundle\Service;

use Povs\ListerBundle\Declaration\ListInterface;
use Povs\ListerBundle\DependencyInjection\Configuration;
use Povs\ListerBundle\Exception\ListException;
use Symfony\Component\Config\Definition\Processor;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ConfigurationResolver
{
    /**
     * @var array
     */
    private $defaultConfiguration;

    /**
     * @var array
     */
    private $resolvedConfiguration;

    /**
     * ConfigurationResolver constructor.
     *
     * @param array $defaultConfiguration
     */
    public function __construct(array $defaultConfiguration)
    {
        $this->defaultConfiguration = $defaultConfiguration;
    }

    /**
     * @param ListInterface $list
     */
    public function resolve(ListInterface $list): void
    {
        $configs = $this->defaultConfiguration;

        if ($listConfig = $list->configure()) {
            if (isset($configs[0]['list_config'])) {
                $listConfig = array_replace_recursive($configs[0]['list_config'], $listConfig);
            }

            $configs[0]['list_config'] = $listConfig;
        }

        $configuration = new Configuration();
        $processor = new Processor();
        $this->resolvedConfiguration = $processor->processConfiguration($configuration, $configs);
    }

    /**
     * @param string $type type name
     *
     * @return string fully qualified list type class name
     */
    public function getListTypeClass(string $type): string
    {
        if (!isset($this->resolvedConfiguration['types'][$type])) {
            throw ListException::listTypeNotConfigured($type);
        }

        return $this->resolvedConfiguration['types'][$type];
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public function getTypeConfiguration(string $type): array
    {
        if (!isset($this->resolvedConfiguration['list_config']['type_configuration'][$type])) {
            return [];
        }

        return $this->resolvedConfiguration['list_config']['type_configuration'][$type] ?? [];
    }

    /**
     * @param string $name param name
     *
     * @return string|null
     */
    public function getRequestConfiguration(string $name): ?string
    {
        return $this->resolvedConfiguration['list_config']['request'][$name];
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->resolvedConfiguration['list_config']['identifier'];
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->resolvedConfiguration['list_config']['alias'];
    }

    /**
     * @return bool
     */
    public function getTranslate(): bool
    {
        return $this->resolvedConfiguration['list_config']['translate'];
    }

    /**
     * @return string|null
     */
    public function getTranslationDomain(): ?string
    {
        return $this->resolvedConfiguration['list_config']['translation_domain'];
    }

    /**
     * @return array
     */
    public function getFormConfiguration(): array
    {
        return $this->resolvedConfiguration['list_config']['form_configuration'];
    }

    /**
     * @return bool
     */
    public function isMultiColumnSortable(): bool
    {
        return $this->resolvedConfiguration['list_config']['multi_column_sort'];
    }
}