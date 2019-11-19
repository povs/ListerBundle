<?php
namespace Povs\ListerBundle\Factory;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\Service\ConfigurationResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class PaginatorFactoryTest extends TestCase
{
    public function testBuildPaginator(): void
    {
        $configMock = $this->createMock(ConfigurationResolver::class);
        $configMock->expects($this->once())
            ->method('getAlias')
            ->willReturn('alias');
        $configMock->expects($this->once())
            ->method('getIdentifier')
            ->willReturn('id');
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $factory = new PaginatorFactory($configMock);
        $factory->buildPaginator($queryBuilderMock);
    }
}