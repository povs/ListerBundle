<?php
namespace Povs\ListerBundle\Type\SelectorType;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class CountSelectorTypeTest extends TestCase
{
    public function testApply(): CountSelectorType
    {
        $selectorType = new CountSelectorType();
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->expects($this->exactly(2))
            ->method('addSelect')
            ->withConsecutive(
                ['count(foo) as id_field_0'],
                ['count(bar) as id_field_1']
            );

        $selectorType->apply($queryBuilderMock, ['foo', 'bar'], 'id');

        return $selectorType;
    }

    /**
     * @depends testApply
     * @param CountSelectorType $selectorType
     */
    public function testGetValue(CountSelectorType $selectorType): void
    {
        $data = [
            'id_field_0' => '4',
            'id_field_1' => null
        ];

        $this->assertEquals([4, 0], $selectorType->getValue($data, 'id'));
    }

    public function testGetSortPath(): void
    {
        $basicSelectorType = new CountSelectorType();
        $this->assertEquals('id_field_0', $basicSelectorType->getSortPath('id'));
    }
}