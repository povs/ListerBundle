<?php
namespace Povs\ListerBundle\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\Type\ListType\ArrayListType;
use Povs\ListerBundle\Type\ListType\CsvListType;
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
            'types' => [
                'list' => ArrayListType::class
            ],
            'list_config' => [
                'identifier' => 'id',
                'alias' => 'l',
                'translate' => false,
                'translation_domain' => null,
                'multi_column_sort' => false,
                'form_configuration' => [],
                'request' => [
                    'page' => 'page',
                    'length' => 'length',
                    'sort' => 'sort',
                    'filter' => null
                ],
                'type_configuration' => []
            ],
        ];

        $config = $this->processor->processConfiguration($this->configuration, []);
        $this->assertEquals($defaultConfig, $config);
    }

    public function testCustomConfig(): void
    {
        $customConfig = [
            'types' => [
                'list' => ArrayListType::class,
                'csv' => CsvListType::class
            ],
            'list_config' => [
                'translate' => true,
                'multi_column_sort' => true,
                'form_configuration' => [
                    'config' => 'value'
                ],
                'request' => [
                    'filter' => 'new_filter'
                ],
                'type_configuration' => [
                    'list' => [
                        'length' => 1000,
                        'limit' => 10000
                    ]
                ]
            ],
        ];

        $expectedConfig = [
            'types' => [
                'list' => ArrayListType::class,
                'csv' => CsvListType::class
            ],
            'list_config' => [
                'identifier' => 'id',
                'alias' => 'l',
                'translate' => true,
                'translation_domain' => null,
                'multi_column_sort' => true,
                'form_configuration' => [
                    'config' => 'value'
                ],
                'request' => [
                    'page' => 'page',
                    'length' => 'length',
                    'sort' => 'sort',
                    'filter' => 'new_filter'
                ],
                'type_configuration' => [
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
}