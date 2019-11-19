<?php
namespace Povs\ListerBundle\Type\SelectorType;

use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\Exception\ListException;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class CountSelectorTypeTest extends TestCase
{
    /**
     * @var CountSelectorType
     */
    private $countSelectorType;

    public function setUp()
    {
        $this->countSelectorType = new CountSelectorType();
    }

    /**
     * @dataProvider getStatementProvider
     * @param array       $paths
     * @param string|null $expected
     * @param string|null $exception
     */
    public function testGetStatement(array $paths, ?string $expected, ?string $exception): void
    {
        if ($exception) {
            $this->expectException($exception);
        }

        $res = $this->countSelectorType->getStatement($paths);
        $this->assertEquals($expected, $res);
    }

    /**
     * @dataProvider getValueProvider
     * @param string|null  $value
     * @param mixed        $expectedValue
     */
    public function testGetValue(?string $value, $expectedValue): void
    {
        $this->assertEquals($expectedValue, $this->countSelectorType->getValue($value));
    }

    /**
     * @return array
     */
    public function getStatementProvider(): array
    {
        return [
            [['foo'], 'count(foo)', null],
            [['foo', 'bar'], 'null', ListException::class],
        ];
    }

    /**
     * @return array
     */
    public function getValueProvider(): array
    {
        return [
            [null, 0],
            ['10', 10]
        ];
    }
}