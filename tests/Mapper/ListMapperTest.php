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
            ->willReturn($typeOptions);
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

    public function testAddFieldPosition(): void
    {
        $fields = [
            ['field_1', ['label' => 'test']],
            ['field_2', ['label' => 'test']],
            ['field_3', ['label' => 'test', 'position' => 'field_2']],
            ['field_4', ['label' => 'test', 'position' => 'invalid_id']]
        ];

        $fieldTypeLocatorMock = $this->createMock(FieldTypeLocator::class);
        $mapper = new ListMapper($fieldTypeLocatorMock, 'listType', null);

        foreach ($fields as $field) {
            $mapper->add($field[0], null, $field[1]);
        }

        $fields = $mapper->getFields();
        $this->assertEquals('field_1', $fields->first()->getId());
        $this->assertEquals('field_3', $fields->next()->getId());
        $this->assertEquals('field_2', $fields->next()->getId());
        $this->assertEquals('field_4', $fields->next()->getId());
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

    public function testGetFields(): void
    {
        $fields = [
            ['field_1', ['label' => 'test', 'lazy' => true]],
            ['field_2', ['label' => 'test']],
            ['field_3', ['label' => 'test']],
        ];

        $fieldTypeLocatorMock = $this->createMock(FieldTypeLocator::class);
        $mapper = new ListMapper($fieldTypeLocatorMock, 'listType', null);

        foreach ($fields as $field) {
            $mapper->add($field[0], null, $field[1]);
        }

        $this->assertCount(1, $mapper->getFields(true));
        $this->assertCount(2, $mapper->getFields(false));
        $this->assertCount(3, $mapper->getFields());
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
