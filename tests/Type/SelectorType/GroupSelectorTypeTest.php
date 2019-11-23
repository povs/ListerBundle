<?php
namespace Povs\ListerBundle\Type\SelectorType;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class GroupSelectorTypeTest extends TestCase
{
    /**
     * @var GroupSelectorType
     */
    private $groupSelectorType;

    public function setUp()
    {
        $this->groupSelectorType = new GroupSelectorType();
    }

    public function testApply(): GroupSelectorType
    {
        $selectorType = new GroupSelectorType();
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->expects($this->exactly(3))
            ->method('addSelect')
            ->withConsecutive(
                ['GROUP_CONCAT(foo SEPARATOR \'|-|\') as id_field_0'],
                ['GROUP_CONCAT(bar SEPARATOR \'|-|\') as id_field_1'],
                ['GROUP_CONCAT(test SEPARATOR \'|-|\') as id_field_2']
            );

        $selectorType->apply($queryBuilderMock, ['foo', 'bar', 'test'], 'id');

        return $selectorType;
    }

    /**
     * @depends testApply
     * @param GroupSelectorType $selectorType
     */
    public function testGetValue(GroupSelectorType $selectorType): void
    {
        $data = [
            'id_field_0' => 'res1|-|res2|-|res3',
            'id_field_1' => null,
            'id_field_2' => ''
        ];

        $this->assertEquals([['res1', 'res2', 'res3'], [], []], $selectorType->getValue($data, 'id'));
    }

    public function testGetSortPath(): void
    {
        $basicSelectorType = new BasicSelectorType();
        $this->assertEquals('id_field_0', $basicSelectorType->getSortPath('id'));
    }
}