<?php
namespace Povs\ListerBundle\Type\SelectorType;

use PHPUnit\Framework\TestCase;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class BasicSelectorTypeTest extends TestCase
{
    /**
     * @var BasicSelectorType
     */
    private $basicSelectorType;

    public function setUp()
    {
        $this->basicSelectorType = new BasicSelectorType();
    }

    /**
     * @dataProvider getStatementProvider
     * @param array       $paths
     * @param string|null $delimiter
     * @param string      $expectedPath
     */
    public function testGetStatement(array $paths, ?string $delimiter, string $expectedPath): void
    {
        $res = $delimiter
            ? $this->basicSelectorType->getStatement($paths, $delimiter)
            : $this->basicSelectorType->getStatement($paths);

        $this->assertEquals($expectedPath, $res);
    }

    /**
     * @dataProvider getValueProvider
     * @param string|null  $value
     * @param array|string $expectedValue
     */
    public function testGetValue(?string $value, $expectedValue): void
    {
        $this->assertEquals($expectedValue, $this->basicSelectorType->getValue($value));
    }

    /**
     * @return array
     */
    public function getStatementProvider(): array
    {
        return [
            [['foo'], null, 'foo'],
            [['foo', 'bar'], null, 'CONCAT(foo,\'|-|\',bar)'],
            [['foo', 'bar'], '.', 'CONCAT(foo,\'.\',bar)'],
        ];
    }

    /**
     * @return array
     */
    public function getValueProvider(): array
    {
        return [
            ['', null],
            ['foo', 'foo'],
            ['foo|-|bar', ['foo', 'bar']]
        ];
    }
}