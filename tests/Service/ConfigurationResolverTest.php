<?php

namespace Povs\ListerBundle\Service;

use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\Declaration\ListInterface;
use Povs\ListerBundle\Exception\ListException;
use Povs\ListerBundle\Type\ListType\ArrayListType;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ConfigurationResolverTest extends TestCase
{
    private static $defaultConfig = [
        'types' => [
            'list' => ArrayListType::class,
        ],
        'list_config' => [
            'identifier' => 'id',
            'alias' => 'l',
            'translate' => false,
            'translation_domain' => null,
            'form_configuration' => [],
            'multi_column_sort' => false,
            'request' => [
                'page' => 'page',
                'length' => 'length',
                'sort' => 'sort',
                'filter' => null,
            ],
            'type_configuration' => []
        ]
    ];

    private static $config = [
        'alias' => 'c',
        'form_configuration' => [
            'form_config' => true,
            'form_config2' => false
        ],
        'request' => [
            'filter' => 'filter'
        ],
        'multi_column_sort' => true,
        'type_configuration' => [
            'list' => [
                'test1' => true,
                'test2' => 'value'
            ]
        ]
    ];

    /**
     * @return ConfigurationResolver
     */
    public function testResolve(): ConfigurationResolver
    {
        $resolver = new ConfigurationResolver([self::$defaultConfig]);
        $mock = $this->createMock(ListInterface::class);
        $mock->expects($this->once())
            ->method('configure')
            ->willReturn(self::$config);

        $resolver->resolve($mock);

        return $resolver;
    }

    /**
     * @depends testResolve
     *
     * @param ConfigurationResolver $resolver
     */
    public function testGetListTypeClass(ConfigurationResolver $resolver): void
    {
        $this->assertEquals(
            ArrayListType::class,
            $resolver->getListTypeClass('list')
        );
    }

    /**
     * @depends testResolve
     *
     * @param ConfigurationResolver $resolver
     */
    public function testGetListTypeClassThrowsException(ConfigurationResolver $resolver): void
    {
        $this->expectException(ListException::class);
        $this->expectExceptionMessage('List type "test" is not configured');

        $resolver->getListTypeClass('test');
    }

    /**
     * @depends testResolve
     *
     * @param ConfigurationResolver $resolver
     */
    public function testGetTypeConfiguration(ConfigurationResolver $resolver): void
    {
        $expects = [
            'test1' => true,
            'test2' => 'value'
        ];

        $this->assertEquals($expects, $resolver->getTypeConfiguration('list'));
    }

    /**
     * @depends testResolve
     *
     * @param ConfigurationResolver $resolver
     */
    public function testGetTypeConfigurationNull(ConfigurationResolver $resolver): void
    {
        $this->assertEquals([], $resolver->getTypeConfiguration('test'));
    }

    /**
     * @depends testResolve
     *
     * @param ConfigurationResolver $resolver
     */
    public function testGetRequestConfiguration(ConfigurationResolver $resolver): void
    {
        $expects = [
            'page' => 'page',
            'length' => 'length',
            'sort' => 'sort',
            'filter' => 'filter',
        ];

        foreach ($expects as $key => $val) {
            $this->assertEquals($val, $resolver->getRequestConfiguration($key));
        }
    }

    /**
     * @depends testResolve
     *
     * @param ConfigurationResolver $resolver
     */
    public function testGetIdentifier(ConfigurationResolver $resolver): void
    {
        $this->assertEquals('id', $resolver->getIdentifier());
    }

    /**
     * @depends testResolve
     * @param ConfigurationResolver $resolver
     */
    public function testGetAlias(ConfigurationResolver $resolver): void
    {
        $this->assertEquals('c', $resolver->getAlias());
    }

    /**
     * @depends testResolve
     * @param ConfigurationResolver $resolver
     */
    public function testGetTranslate(ConfigurationResolver $resolver): void
    {
        $this->assertFalse($resolver->getTranslate());
    }

    /**
     * @depends testResolve
     * @param ConfigurationResolver $resolver
     */
    public function testGetTranslationDomain(ConfigurationResolver $resolver): void
    {
        $this->assertNull($resolver->getTranslationDomain());
    }

    /**
     * @depends testResolve
     * @param ConfigurationResolver $resolver
     */
    public function testGetFormConfiguration(ConfigurationResolver $resolver): void
    {
        $expected = [
            'form_config' => true,
            'form_config2' => false
        ];

        $this->assertEquals($expected, $resolver->getFormConfiguration());
    }

    /**
     * @depends testResolve
     * @param ConfigurationResolver $resolver
     */
    public function testIsMultiColumnSortable(ConfigurationResolver $resolver): void
    {
        $this->assertTrue($resolver->isMultiColumnSortable());
    }
}
