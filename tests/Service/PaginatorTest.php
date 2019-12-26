<?php
namespace Povs\ListerBundle\Service;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Exception;
use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\Exception\ListQueryException;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class PaginatorTest extends TestCase
{
    public function testGetCount(): Paginator
    {
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryMock = $this->createMock(AbstractQuery::class);
        $queryBuilderMock->expects($this->once())
            ->method('select')
            ->with('COUNT(DISTINCT foo.bar)')
            ->willReturnSelf();
        $queryBuilderMock->expects($this->once())
            ->method('distinct')
            ->with(false)
            ->willReturnSelf();
        $queryBuilderMock->expects($this->exactly(2))
            ->method('resetDQLPart')
            ->withConsecutive(['orderBy'], ['groupBy'])
            ->willReturnSelf();
        $queryBuilderMock->expects($this->once())
            ->method('getQuery')
            ->willReturn($queryMock);
        $queryMock->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn(100);

        $paginator = new Paginator($queryBuilderMock, 'foo', 'bar');
        $this->assertEquals(100, $paginator->getCount());

        return $paginator;
    }

    /**
     * @depends testGetCount
     *
     * @param Paginator $paginator
     */
    public function testGetCountWillNotQueryTwice(Paginator $paginator): void
    {
        $this->assertEquals(100, $paginator->getCount());
    }

    public function testGetCountException(): void
    {
        $this->expectException(ListQueryException::class);
        $this->expectExceptionMessage('Query error: foo. DQL: bar');
        $this->expectExceptionCode(500);
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->expects($this->once())
            ->method('select')
            ->willThrowException(new Exception('foo'));
        $queryBuilderMock->expects($this->once())
            ->method('getDQL')
            ->willReturn('bar');

        $paginator = new Paginator($queryBuilderMock, 'foo', 'bar');
        $paginator->getCount();
    }

    public function testGetData(): void
    {
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryMock = $this->createMock(AbstractQuery::class);
        $queryBuilderMock->expects($this->once())
            ->method('setFirstResult')
            ->with(5)
            ->willReturnSelf();
        $queryBuilderMock->expects($this->once())
            ->method('setMaxResults')
            ->with(20)
            ->willReturnSelf();
        $queryBuilderMock->expects($this->once())
            ->method('getQuery')
            ->willReturn($queryMock);
        $queryMock->expects($this->once())
            ->method('getResult')
            ->willReturn(['result']);

        $paginator = new Paginator($queryBuilderMock, 'foo', 'bar');
        $this->assertEquals(['result'], $paginator->getData(5, 20));
    }

    public function testGetDataException(): void
    {
        $this->expectException(ListQueryException::class);
        $this->expectExceptionMessage('Query error: foo. DQL: bar');
        $this->expectExceptionCode(500);

        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->expects($this->once())
            ->method('setFirstResult')
            ->willThrowException(new Exception('foo'));
        $queryBuilderMock->expects($this->once())
            ->method('getDQL')
            ->willReturn('bar');

        $paginator = new Paginator($queryBuilderMock, 'foo', 'bar');
        $paginator->getData(5, 20);
    }
}