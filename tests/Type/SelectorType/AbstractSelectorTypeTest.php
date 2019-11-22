<?php
namespace Povs\ListerBundle\Type\SelectorType;

use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class AbstractSelectorTypeTest extends TestCase
{
    /**
     * @var AbstractSelectorType
     */
    private $selector;

    public function setUp()
    {
        $this->selector = $this->getMockForAbstractClass(AbstractSelectorType::class);
    }

    public function testApply(): void
    {
        $this->selector->expects($this->once())
            ->method('getStatement')
            ->with('foo')
            ->willReturn('statement');
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->once())
            ->method('addSelect')
            ->with('statement as id_field_0');

        $this->selector->apply($queryBuilder, ['foo'], 'id');
    }

    public function testGetValue(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $this->selector->apply($queryBuilder, ['foo'], 'id');
        $this->selector->expects($this->once())
            ->method('processValue')
            ->with('data')
            ->willReturn('processed');
        $data = ['id_field_0' => 'data'];
        $this->assertEquals('processed', $this->selector->getValue($data, 'id'));
    }

    public function testGetSortPath(): void
    {
        $this->assertEquals('id_field_0', $this->selector->getSortPath('id'));
    }
}