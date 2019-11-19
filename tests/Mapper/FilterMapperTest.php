<?php
namespace Povs\ListerBundle\Mapper;

use Povs\ListerBundle\DependencyInjection\Locator\FilterTypeLocator;
use Povs\ListerBundle\Type\FilterType\FilterTypeInterface;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class FilterMapperTest extends AbstractMapperTest
{
    public function testAddFieldCreatedWithType(): FilterField
    {
        $id = 'test_id';
        $typeOptions = [
            'join_type' => 'LEFT',
            'input_type' => 'input_type',
            'input_options' => ['option1' => 'option1', 'option2' => 'option2']
        ];
        $fieldOptions = [
            'input_type' => 'another_type',
            'input_options' => ['another_option1']
        ];
        $fieldTypeMock = $this->createMock(FilterTypeInterface::class);
        $fieldTypeMock->expects($this->once())
            ->method('getDefaultOptions')
            ->willReturn($typeOptions)
            ->getMatcher();
        $fieldTypeLocatorMock = $this->createMock(FilterTypeLocator::class);
        $fieldTypeLocatorMock->expects($this->once())
            ->method('get')
            ->with('fieldType')
            ->willReturn($fieldTypeMock);

        $mapper = new FilterMapper($fieldTypeLocatorMock);
        $mapper->add($id, 'fieldType', $fieldOptions);

        $this->assertTrue($mapper->has($id));

        return $mapper->get($id);
    }

    /**
     * @depends testAddFieldCreatedWithType
     * @param FilterField $field
     */
    public function testAddOptionsSetCorrectlyWithType(FilterField $field): void
    {
        $this->assertEquals('LEFT', $field->getOption('join_type'));
        $this->assertEquals('another_type', $field->getOption('input_type'));
        $this->assertEquals(['another_option1'], $field->getOption('input_options'));
    }

    /**
     * @depends testAddFieldCreatedWithType
     * @param FilterField $field
     */
    public function testAddTypePassedToField(FilterField $field): void
    {
        $this->assertNotNull($field->getType());
        $this->assertInstanceOf(FilterTypeInterface::class, $field->getType());
    }

    public function testAddFieldCreatedWithoutType(): FilterField
    {
        $id = 'test_id';
        $fieldOptions = [
            'input_type' => 'another_type',
            'input_options' => ['another_option1']
        ];

        $fieldTypeLocatorMock = $this->createMock(FilterTypeLocator::class);
        $fieldTypeLocatorMock
            ->expects($this->never())
            ->method('get');

        $mapper = new FilterMapper($fieldTypeLocatorMock);
        $mapper->add($id, null, $fieldOptions);
        $this->assertTrue($mapper->has($id));

        return $mapper->get($id);
    }

    /**
     * @depends testAddFieldCreatedWithoutType
     * @param FilterField $field
     */
    public function testAddOptionsSetCorrectlyWithOutType(FilterField $field): void
    {
        $this->assertEquals('another_type', $field->getOption('input_type'));
        $this->assertEquals(['another_option1'], $field->getOption('input_options'));
    }

    /**
     * @depends testAddFieldCreatedWithoutType
     * @param FilterField $field
     */
    public function testAddTypeNotPassedToField(FilterField $field): void
    {
        $this->assertNull($field->getType());
    }

    public function testGetValue(): void
    {
        $mock = $this->createMock(FilterTypeLocator::class);
        $mapper = new FilterMapper($mock);
        $mapper->add('id', null, ['value' => 'test']);
        $this->assertEquals('test', $mapper->getValue('id'));
    }
    
    public function testSetValue(): void
    {
        $mock = $this->createMock(FilterTypeLocator::class);
        $mapper = new FilterMapper($mock);
        $mapper->add('id', null, ['value' => 'test']);
        $mapper->setValue('id', 'new_value');
        $this->assertEquals('new_value', $mapper->getValue('id'));
    }

    /**
     * @inheritDoc
     */
    protected function getMapper(array $ids): AbstractMapper
    {
        $filterTypeLocator = $this->createMock(FilterTypeLocator::class);
        $mapper = new FilterMapper($filterTypeLocator);

        foreach ($ids as $id) {
            $mapper->add($id, null, []);
        }

        return $mapper;
    }
}