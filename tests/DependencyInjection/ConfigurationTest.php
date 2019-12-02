<?php
namespace Povs\ListerBundle\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\Type\ListType\ArrayListType;
use Povs\ListerBundle\Type\ListType\CsvListType;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Processor;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ConfigurationTest extends TestCase
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var Processor
     */
    private $processor;

    public function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->processor = new Processor();
    }

    public function testDefaultConfig(): void
    {
        $defaultConfig = [
            'default_type' => 'list',
            'types' => [
                'list' => ArrayListType::class
            ],
            'list_config' => [
                'identifier' => 'id',
                'alias' => 'l',
                'translate' => false,
                'translation_domain' => null,
                'form_configuration' => [],
                'request' => [
                    'page' => 'page',
                    'length' => 'length',
                    'sort' => 'sort',
                    'filter' => null
                ],
                'types' => []
            ],
        ];

        $config = $this->processor->processConfiguration($this->configuration, []);
        $this->assertEquals($defaultConfig, $config);
    }

    public function testCustomConfig(): void
    {
        $customConfig = [
            'default_type' => 'csv',
            'types' => [
                'list' => ArrayListType::class,
                'csv' => CsvListType::class
            ],
            'list_config' => [
                'translate' => true,
                'form_configuration' => [
                    'config' => 'value'
                ],
                'request' => [
                    'filter' => 'new_filter'
                ],
                'types' => [
                    'list' => [
                        'length' => 1000,
                        'limit' => 10000
                    ]
                ]
            ],
        ];

        $expectedConfig = [
            'default_type' => 'csv',
            'types' => [
                'list' => ArrayListType::class,
                'csv' => CsvListType::class
            ],
            'list_config' => [
                'identifier' => 'id',
                'alias' => 'l',
                'translate' => true,
                'translation_domain' => null,
                'form_configuration' => [
                    'config' => 'value'
                ],
                'request' => [
                    'page' => 'page',
                    'length' => 'length',
                    'sort' => 'sort',
                    'filter' => 'new_filter'
                ],
                'types' => [
                    'list' => [
                        'length' => 1000,
                        'limit' => 10000
                    ]
                ]
            ],
        ];

        $config = $this->processor->processConfiguration($this->configuration, ['povs_lister' => $customConfig]);
        $this->assertEquals($expectedConfig, $config);
    }

    public function testVersion(): void
    {
        $configuration = new Configuration();
        $configTreeBuilderV4 = $configuration->getConfigTreeBuilder(4);
        $configTreeBuilderV3 = $configuration->getConfigTreeBuilder(3);

        $this->assertInstanceOf(ArrayNodeDefinition::class, $configTreeBuilderV4->getRootNode());
        $this->assertInstanceOf(ArrayNodeDefinition::class, $configTreeBuilderV3->getRootNode());
    }
}