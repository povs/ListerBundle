<?php
namespace Povs\ListerBundle\Mapper;

use Povs\ListerBundle\Exception\ListFieldException;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class JoinFieldTest extends AbstractFieldTest
{
    private static $passedOptions = [];

    private static $expectedOptions = [
        'join_type' => 'INNER'
    ];

    public function testGetJoinPath(): void
    {
        $parent = new JoinField('parent_path', 'parent_prop', 'parent_alias', [], null);
        $child = new JoinField('child_path', 'child_prop', 'child_alias', [], $parent);

        $this->assertEquals('alias.parent_prop', $parent->getJoinPath('alias'));
        $this->assertEquals('parent_alias.child_prop', $child->getJoinPath('alias'));
    }

    public function testGetProperty(): void
    {
        $field = new JoinField('path', 'prop', 'alias', [], null);
        $this->assertEquals('prop', $field->getProperty());
    }

    public function testGetAlias(): void
    {
        $field = new JoinField('path', 'prop', 'alias', [], null);
        $this->assertEquals('alias', $field->getAlias());
    }

    public function testSetAlias(): void
    {
        $field = new JoinField('path', 'prop', 'alias', [], null);
        $field->setAlias('new_alias');
        $this->assertEquals('new_alias', $field->getAlias());
    }

    public function testOptions(): void
    {
        $field = new JoinField('path', 'prop', 'alias', self::$passedOptions, null);

        foreach (self::$expectedOptions as $option => $value) {
            $this->assertEquals($value, $field->getOption($option));
        }
    }

    public function testInvalidOptionsThrowsException(): void
    {
        $this->expectException(ListFieldException::class);
        $this->getField(['id', 'id', 'id', ['invalid_option' => true], null]);
    }

    /**
     * @return array [data[], expectedId]
     */
    public function idProvider(): array
    {
        return [
            [['id', 'id', 'id', [], null], 'id'],
            [['test.dot.conversion', 'id', 'id', [], null], 'test_dot_conversion']
        ];
    }

    /**
     * @return array [data[], expectedPaths[], exception]
     */
    public function pathsProvider(): array
    {
        return [
            [['id', 'id', 'id', [], null], ['id'], null]
        ];
    }

    /**
     * @param array $data
     * @return JoinField
     */
    protected function getField(array $data = null): AbstractField
    {
        return new JoinField($data[0], $data[1], $data[2], $data[3], $data[4]);
    }

    /**
     * @param AbstractField|JoinField $field
     *
     * @return array
     */
    protected function getPaths(AbstractField $field): array
    {
        return [$field->getPath()];
    }
}