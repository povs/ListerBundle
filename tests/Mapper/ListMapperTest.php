<?php
namespace Povs\ListerBundle\Tests\Mapper;

use Povs\ListerBundle\DependencyInjection\Locator\FieldTypeLocator;
use Povs\ListerBundle\Exception\ListException;
use Povs\ListerBundle\Mapper\AbstractMapper;
use Povs\ListerBundle\Mapper\AbstractMapperTest;
use Povs\ListerBundle\Mapper\ListField;
use Povs\ListerBundle\Mapper\ListMapper;
use Povs\ListerBundle\Type\FieldType\FieldTypeInterface;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListMapperTest extends AbstractMapperTest
{
    public function testAddFieldCreatedWithType(): ListField
    {
        $id = 'test_id';
        $typeOptions = [
            'sortable' => true
        ];
        $fieldOptions = [
            'label' => 'test'
        ];

        $fieldTypeMock = $this->createMock(FieldTypeInterface::class);
        $fieldTypeMock->expects($this->once())
            ->method('getDefaultOptions')
            ->with('listType')
            ->willReturn($typeOptions)
            ->getMatcher();
        $fieldTypeLocatorMock = $this->createMock(FieldTypeLocator::class);
        $fieldTypeLocatorMock->expects($this->once())
            ->method('get')
            ->with('fieldType')
            ->willReturn($fieldTypeMock);

        $mapper = new ListMapper($fieldTypeLocatorMock, 'listType', null);
        $mapper->add($id, 'fieldType', $fieldOptions);

        $this->assertTrue($mapper->has($id));

        return $mapper->get($id);
    }

    /**
     * @depends testAddFieldCreatedWithType
     * @param ListField $field
     */
    public function testAddOptionsSetCorrectlyWithType(ListField $field): void
    {
        $this->assertTrue($field->getOption('sortable'));
        $this->assertEquals('test', $field->getOption('label'));
    }

    /**
     * @depends testAddFieldCreatedWithType
     * @param ListField $field
     */
    public function testAddTypePassedToField(ListField $field): void
    {
        $this->assertNotNull($field->getType());
        $this->assertInstanceOf(FieldTypeInterface::class, $field->getType());
    }

    public function testAddFieldCreatedWithoutType(): ListField
    {
        $id = 'test_id';
        $fieldOptions = [
            'label' => 'test'
        ];

        $fieldTypeLocatorMock = $this->createMock(FieldTypeLocator::class);
        $fieldTypeLocatorMock
            ->expects($this->never())
            ->method('get');

        $mapper = new ListMapper($fieldTypeLocatorMock, 'listType', null);
        $mapper->add($id, null, $fieldOptions);
        $this->assertTrue($mapper->has($id));

        return $mapper->get($id);
    }

    /**
     * @depends testAddFieldCreatedWithoutType
     * @param ListField $field
     */
    public function testAddOptionsSetCorrectlyWithOutType(ListField $field): void
    {
        $this->assertEquals('test', $field->getOption('label'));
    }

    /**
     * @depends testAddFieldCreatedWithoutType
     * @param ListField $field
     */
    public function testAddTypeNotPassedToField(ListField $field): void
    {
        $this->assertNull($field->getType());
    }

    public function testBuild(): void
    {
        $fields = [
            'field_1',
            'field_2',
            'field_3'
        ];

        $fieldTypeLocatorMock = $this->createMock(FieldTypeLocator::class);
        $parent = new ListMapper($fieldTypeLocatorMock, 'listType', null);

        foreach ($fields as $field) {
            $parent->add($field, null, ['label' => 'test']);
        }

        $child = new ListMapper($fieldTypeLocatorMock, 'listType', $parent);
        $child->build();

        $this->assertEquals($parent->getFields(), $child->getFields());
    }

    public function testBuildWithoutParentThrowsException(): void
    {
        $this->expectException(ListException::class);
        $this->expectExceptionMessage('List is not yet configured to be copied.');
        $this->expectExceptionCode(500);
        $fieldTypeLocatorMock = $this->createMock(FieldTypeLocator::class);
        $parent = new ListMapper($fieldTypeLocatorMock, 'listType', null);
        $parent->build();
    }

    /**
     * @inheritDoc
     */
    protected function getMapper(array $ids): AbstractMapper
    {
        $fieldTypeLocatorMock = $this->createMock(FieldTypeLocator::class);
        $mapper = new ListMapper($fieldTypeLocatorMock, 'listType', null);

        foreach ($ids as $id) {
            $mapper->add($id, null, ['label' => 'test']);
        }

        return $mapper;
    }
}