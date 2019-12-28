<?php
namespace Povs\ListerBundle\Mapper;

use Povs\ListerBundle\Type\FieldType\FieldTypeInterface;
use Povs\ListerBundle\Type\SelectorType\BasicSelectorType;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListFieldTest extends AbstractFieldTest
{
    private static $passedOptions = [
        'label' => 'test',
        'path' => 'path',
        'property' => ['prop1', 'prop2']
    ];

    private static $expectedOptions = [
        'label' => 'test',
        'sortable' => true,
        'sort_value' => null,
        'sort_path' => null,
        'path' => 'path',
        'join_type' => 'INNER',
        'property' => ['prop1', 'prop2'],
        'selector' => BasicSelectorType::class,
        'view_options' => [],
        'translate' => false,
        'translation_domain' => null,
        'translation_prefix' => null,
        'translate_null' => false,
        'field_type_options' => []
    ];

    public function testGetType(): void
    {
        $type = $this->createMock(FieldTypeInterface::class);
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

    /**
     * @param array $data
     * @return ListField
     */
    protected function getField(array $data = null): AbstractField
    {
        if (!array_key_exists('label', $data[1])) {
            $data[1]['label'] = 'test';
        }

        return new ListField($data[0], $data[1], $data[2]);
    }

    /**
     * @param AbstractField|ListField $field
     *
     * @return array
     */
    protected function getPaths(AbstractField $field): array
    {
        return $field->getPaths();
    }
}