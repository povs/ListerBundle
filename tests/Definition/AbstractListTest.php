<?php
namespace Povs\ListerBundle\Definition;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\Mapper\FilterMapper;
use Povs\ListerBundle\Mapper\JoinMapper;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class AbstractListTest extends TestCase
{
    private $list;

    public function setUp()
    {
        $this->list = $this->getMockForAbstractClass(AbstractList::class);
    }

    public function testSetParameters(): void
    {
        $this->assertNull($this->list->setParameters([]));
    }

    public function testConfigure(): void
    {
        $this->assertEmpty($this->list->configure());
    }

    public function testBuildFilterFields(): void
    {
        $filterMapperMock = $this->createMock(FilterMapper::class);
        $this->assertNull($this->list->buildFilterFields($filterMapperMock));
        $this->assertEmpty($filterMapperMock->getFields());
    }

    public function testBuildJoinFields(): void
    {
        $joinMapperMock = $this->createMock(JoinMapper::class);
        $listValueMock = $this->createMock(ListValueInterface::class);
        $joinMapperMock->expects($this->once())
            ->method('build');
        $this->assertNull($this->list->buildJoinFields($joinMapperMock, $listValueMock));
    }

    public function testConfigureQuery(): void
    {
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $listValueMock = $this->createMock(ListValueInterface::class);
        $this->assertEmpty($queryBuilderMock->getDQLParts());
        $this->assertNull($this->list->configureQuery($queryBuilderMock, $listValueMock));
    }
}