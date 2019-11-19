<?php
namespace Povs\ListerBundle\Factory;

use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\Definition\AbstractList;
use Povs\ListerBundle\Definition\ListValueInterface;
use Povs\ListerBundle\DependencyInjection\Locator\FieldTypeLocator;
use Povs\ListerBundle\DependencyInjection\Locator\FilterTypeLocator;
use Povs\ListerBundle\Mapper\FilterMapper;
use Povs\ListerBundle\Mapper\JoinMapper;
use Povs\ListerBundle\Mapper\ListMapper;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class MapperFactoryTest extends TestCase
{
    private $list;

    public function setUp()
    {
        $this->list = new class() extends AbstractList {
            public function buildListFields(ListMapper $listMapper): void
            {
                $listMapper->add('id1', null, ['label' => 'test'])
                    ->add('id2', null, ['label' => 'test'])
                    ->add('id3', null, ['label' => 'test']);
            }

            public function buildTestFields(ListMapper $listMapper): void
            {
                $listMapper->build();
                $listMapper->add('id4', null, ['label' => 'test']);
            }

            public function buildFilterFields(FilterMapper $filterMapper): void
            {
                $filterMapper->add('id1')
                    ->add('id2');
            }

            public function buildJoinFields(JoinMapper $joinMapper, ListValueInterface $value): void
            {
                $joinMapper->add('id1', 'alias')
                    ->add('id2', 'alias');
            }

            public function getDataClass(): string
            {
                return 'dataClass';
            }
        };
    }

    public function testCreateListMapperWithListType(): void
    {
        $fieldTypeLocatorMock = $this->createMock(FieldTypeLocator::class);
        $filterTypeLocatorMock = $this->createMock(FilterTypeLocator::class);
        $factory = new MapperFactory($fieldTypeLocatorMock, $filterTypeLocatorMock);
        $listMapper = $factory->createListMapper($this->list, 'list');
        $this->assertCount(3, $listMapper->getFields());
    }

    public function testCreateListMapperWithOtherType(): void
    {
        $fieldTypeLocatorMock = $this->createMock(FieldTypeLocator::class);
        $filterTypeLocatorMock = $this->createMock(FilterTypeLocator::class);
        $factory = new MapperFactory($fieldTypeLocatorMock, $filterTypeLocatorMock);
        $listMapper = $factory->createListMapper($this->list, 'test');
        $this->assertCount(4, $listMapper->getFields());
    }

    public function testCreateFilterMapper(): void
    {
        $fieldTypeLocatorMock = $this->createMock(FieldTypeLocator::class);
        $filterTypeLocatorMock = $this->createMock(FilterTypeLocator::class);
        $factory = new MapperFactory($fieldTypeLocatorMock, $filterTypeLocatorMock);
        $filterMapper = $factory->createFilterMapper($this->list);
        $this->assertCount(2, $filterMapper->getFields());
    }

    public function testCreateJoinMapper(): void
    {
        $fieldTypeLocatorMock = $this->createMock(FieldTypeLocator::class);
        $filterTypeLocatorMock = $this->createMock(FilterTypeLocator::class);
        $listMapperMock = $this->createMock(ListMapper::class);
        $filterMapperMock = $this->createMock(FilterMapper::class);
        $listValueMock = $this->createMock(ListValueInterface::class);
        $factory = new MapperFactory($fieldTypeLocatorMock, $filterTypeLocatorMock);
        $joinMapper = $factory->createJoinMapper($this->list, $listMapperMock, $filterMapperMock, $listValueMock);
        $this->assertCount(2, $joinMapper->getFields());
    }
}