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
            ->with('foo IN :bar')
            ->willReturnSelf();
        $queryBuilderMock->expects($this->once())
            ->method('setParameter')
            ->with(':bar', 'value');

        $type = $this->getType(['foo'], 'foo', []);
        $type->filter($queryBuilderMock, 'bar', 'value');
    }

    /**
     * @param array  $paths
     * @param string $path
     * @param array  $options
     *
     * @return ContainsQueryType
     */
    private function getType(array $paths, string $path, array $options): ContainsQueryType
    {
        $type = new ContainsQueryType();
        $type->setPaths($paths);
        $type->setPath($path);
        $type->setOptions($options);

        return $type;
    }
}