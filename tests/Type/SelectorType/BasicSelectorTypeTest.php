<?php
namespace Povs\ListerBundle\Type\SelectorType;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class BasicSelectorTypeTest extends TestCase
{
    public function testApplySinglePath(): BasicSelectorType
    {
        $basicSelectorType = new BasicSelectorType();
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->expects($this->once())
            ->method('addSelect')
            ->with('foo as id_field_0');

        $basicSelectorType->apply($queryBuilderMock, ['foo'], 'id');

        return $basicSelectorType;
    }

    /**
     * @depends testApplySinglePath
     * @param BasicSelectorType $basicSelectorType
     */
    public function testGetValueSinglePath(BasicSelectorType $basicSelectorType): void
    {
        $data = ['id_field_0' => 'foo'];
        $this->assertEquals('foo', $basicSelectorType->getValue($data, 'id'));
    }

    public function testApplyMultiplePaths(): BasicSelectorType
    {
        $basicSelectorType = new BasicSelectorType();
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->expects($this->exactly(2))
            ->method('addSelect')
            ->withConsecutive(
                ['foo as id_field_0'],
                ['bar as id_field_1']
            );

        $basicSelectorType->apply($queryBuilderMock, ['foo', 'bar'], 'id');

        return $basicSelectorType;
    }

    /**
     * @depends testApplyMultiplePaths
     * @param BasicSelectorType $basicSelectorType
     */
    public function testGetValueMultiplePaths(BasicSelectorType $basicSelectorType): void
    {
        $data = [
            'id_field_0' => 'foo',
            'id_field_1' => 'bar'
        ];

        $this->assertEquals(['foo', 'bar'], $basicSelectorType->getValue($data, 'id'));
    }

    public function testGetSortPath(): void
    {
        $basicSelectorType = new BasicSelectorType();
        $this->assertEquals('id_field_0', $basicSelectorType->getSortPath('id'));
    }
}