<?php
namespace Povs\ListerBundle\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use Povs\ListerBundle\Type\FilterType\FilterTypeInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Povs\ListerBundle\Type\QueryType\ComparisonQueryType;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class FilterFieldTest extends AbstractFieldTest
{
    private static $passedOptions = [
        'path' => 'path',
        'property' => ['prop1', 'prop2']
    ];

    private static $expectedOptions = [
        'query_type' => ComparisonQueryType::class,
        'query_options' => [],
        'input_type' => TextType::class,
        'input_options' => [],
        'value' => null,
        'mapped' => true,
        'join_type' => 'INNER',
        'path' => 'path',
        'property' => ['prop1', 'prop2'],
        'required' => false
    ];

    public function testGetType(): void
    {
        $type = $this->createMock(FilterTypeInterface::class);
        $field = $this->getField(['id', [], $type]);
        $this->assertEquals($type, $field->getType());
    }

    public function testOptions(): void
    {
        $field = $this->getField(['id', self::$passedOptions, null]);

        foreach (self::$expectedOptions as $option => $value) {
            $this->assertEquals($value, $field->getOption($option));
        }
    }

    public function testGetValue(): void
    {
        $field = $this->getField(['id', ['value' => 'test'], null]);
        $this->assertEquals('test', $field->getValue());
    }

    public function testHasValueTrue(): void
    {
        $field = $this->getField(['id', ['value' => 'test'], null]);
        $this->assertTrue($field->hasValue());
    }

    public function testHasValueTrueArray(): void
    {
        $field = $this->getField(['id', ['value' => ['test']], null]);
        $this->assertTrue($field->hasValue());
    }

    public function testHasValueTrueArrayCollection(): void
    {
        $field = $this->getField(['id', ['value' => new ArrayCollection(['test'])], null]);
        $this->assertTrue($field->hasValue());
    }

    public function testHasValueFalseEmptyArray(): void
    {
        $field = $this->getField(['id', ['value' => []], null]);
        $this->assertFalse($field->hasValue());
    }

    public function testHasValueFalseNull(): void
    {
        $field = $this->getField(['id', ['value' => null], null]);
        $this->assertFalse($field->hasValue());
    }

    public function testHasValueFalseEmptyCollection(): void
    {
        $field = $this->getField(['id', ['value' => new ArrayCollection([])], null]);
        $this->assertFalse($field->hasValue());
    }

    public function testSetValue(): void
    {
        $field = $this->getField(['id', [], null]);
        $field->setValue('value');
        $this->assertEquals('value', $field->getValue());
    }

    /**
     * @param array $data
     * @return FilterField
     */
    protected function getField(array $data = null): AbstractField
    {
        return new FilterField($data[0], $data[1], $data[2]);
    }

    /**
     * @param AbstractField|FilterField $field
     *
     * @return array
     */
    protected function getPaths(AbstractField $field): array
    {
        return $field->getPaths();
    }
}