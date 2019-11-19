<?php
namespace Povs\ListerBundle\Mapper;

use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\Exception\ListFieldException;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
abstract class AbstractFieldTest extends TestCase
{
    /**
     * @dataProvider idProvider
     * @param array  $data field data
     * @param string $id   expected field id
     */
    public function testId(array $data, string $id): void
    {
        $field = $this->getField($data);
        $this->assertEquals($id, $field->getId());
    }

    /**
     * @dataProvider pathsProvider
     * @param array $data
     * @param array $paths
     * @param string $exception
     */
    public function testPaths(array $data, array $paths, ?string $exception): void
    {
        if ($exception) {
            $this->expectException($exception);
        }

        $field = $this->getField($data);

        if (!$exception) {
            $this->assertEquals($this->getPaths($field), $paths);
        }
    }

    public function testInvalidOptionsThrowsException(): void
    {
        $this->expectException(ListFieldException::class);
        $this->getField(['id', ['invalid_option' => true], null]);
    }

    /**
     * @return array [data[], expectedId]
     */
    public function idProvider(): array
    {
        return [
            [['id', [], null], 'id'],
            [['test.dot.conversion', [], null], 'test_dot_conversion']
        ];
    }

    /**
     * @return array [data[], expectedPaths[], exception]
     */
    public function pathsProvider(): array
    {
        return [
            [['id', [], null], ['id'], null],
            [['test.dot.conversion', [], null], ['test.dot.conversion'], null],
            [['test', ['property' => 'prop'], null], ['test.prop'], null],
            [['test', ['property' => ['prop1', 'prop2']], null], ['test.prop1', 'test.prop2'], null],
            [['test', ['path' => 'customPath', 'property' => ['prop']], null], ['customPath.prop'], null],
            [['test', ['path' => 'customPath', 'property' => ['prop1', 'prop2']], null], ['customPath.prop1', 'customPath.prop2'], null],
            [['test', ['path' => ['customPath1', 'customPath2', 'customPath3'], 'property' => ['prop1', 'prop2']], null], [], ListFieldException::class]
        ];
    }

    /**
     * @param array $data
     *
     * @return AbstractField
     */
    abstract protected function getField(array $data): AbstractField;

    /**
     * @param AbstractField $field
     *
     * @return array
     */
    abstract protected function getPaths(AbstractField $field): array;
}