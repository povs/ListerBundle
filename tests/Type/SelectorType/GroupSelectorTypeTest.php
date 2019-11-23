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

    public function testApplySinglePath(): GroupSelectorType
    {
        $selectorType = new GroupSelectorType();
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->expects($this->once())
            ->method('addSelect')
            ->with('GROUP_CONCAT(foo SEPARATOR \'|-|\') as id_field_0');

        $selectorType->apply($queryBuilderMock, ['foo'], 'id');

        return $selectorType;
    }

    /**
     * @depends testApplySinglePath
     * @param GroupSelectorType $selectorType
     */
    public function testGetValueSinglePath(GroupSelectorType $selectorType): void
    {
        $data = ['id_field_0' => 'res1|-|res2|-|res3'];
        $expected = ['res1', 'res2', 'res3'];
        $this->assertEquals($expected, $selectorType->getValue($data, 'id'));
    }

    public function testApplyMultiplePaths(): GroupSelectorType
    {
        $selectorType = new GroupSelectorType();
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->expects($this->once())
            ->method('addSelect')
            ->with('GROUP_CONCAT(IFNULL(foo, \'\'),\'|,|\',IFNULL(bar, \'\'),\'|,|\',IFNULL(test, \'\') SEPARATOR \'|-|\') as id_field_0');

        $selectorType->apply($queryBuilderMock, ['foo', 'bar', 'test'], 'id');

        return $selectorType;
    }

    /**
     * @depends testApplyMultiplePaths
     * @param GroupSelectorType $selectorType
     */
    public function testGetValueMultiplePaths(GroupSelectorType $selectorType): void
    {
        $data = ['id_field_0' => 'res11|,|res12|,|res13|-|res21|,||,|res23|-||,||,|res33'];
        $expected = [['res11', 'res12', 'res13'], ['res21', null, 'res23'], [null, null, 'res33']];
        $this->assertEquals($expected, $selectorType->getValue($data, 'id'));
    }

    public function testGetSortPath(): void
    {
        $basicSelectorType = new GroupSelectorType();
        $this->assertEquals('id_field_0', $basicSelectorType->getSortPath('id'));
    }
}