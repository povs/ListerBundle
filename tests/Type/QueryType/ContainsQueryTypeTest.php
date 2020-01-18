<?php

namespace Povs\ListerBundle\Type\QueryType;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ContainsQueryTypeTest extends TestCase
{
    public function testFilter(): void
    {
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->expects($this->once())
            ->method('andWhere')
            ->with('foo IN (:bar)')
            ->willReturnSelf();
        $queryBuilderMock->expects($this->once())
            ->method('setParameter')
            ->with(':bar', 'value');

        $type = $this->getType([]);
        $type->filter($queryBuilderMock, ['foo'], 'bar', 'value');
    }

    public function testHasAggregation(): void
    {
        $this->assertFalse($this->getType([])->hasAggregation());
    }

    /**
     * @param array $options
     *
     * @return ContainsQueryType
     */
    private function getType(array $options): ContainsQueryType
    {
        $type = new ContainsQueryType();
        $type->setOptions($options);

        return $type;
    }
}
