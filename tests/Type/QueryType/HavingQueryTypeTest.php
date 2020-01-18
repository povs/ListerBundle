<?php

namespace Povs\ListerBundle\Type\QueryType;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class HavingQueryTypeTest extends TestCase
{
    public function testFilter(): void
    {
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->expects($this->once())
            ->method('andHaving')
            ->with('avg(foo) >= :bar')
            ->willReturnSelf();
        $queryBuilderMock->expects($this->once())
            ->method('setParameter')
            ->with(':bar', 50);

        $type = $this->getType(['type' => '>=', 'function' => 'avg']);
        $type->filter($queryBuilderMock, ['foo'], 'bar', 50);
    }

    public function testConfigureOptions(): void
    {
        $optionResolver = new OptionsResolver();
        $type = $this->getType([]);
        $type->configureOptions($optionResolver);

        $this->assertEquals(['type' => '>=', 'function' => 'avg'], $optionResolver->resolve(['type' => '>=', 'function' => 'avg']));
    }

    public function testConfigureOptionsDefault(): void
    {
        $optionResolver = new OptionsResolver();
        $type = $this->getType([]);
        $type->configureOptions($optionResolver);

        $this->assertEquals(['type' => '=', 'function' => 'count'], $optionResolver->resolve());
    }

    public function testHasAggregation(): void
    {
        $this->assertTrue($this->getType([])->hasAggregation());
    }

    /**
     * @param array $options
     *
     * @return HavingQueryType
     */
    private function getType(array $options): HavingQueryType
    {
        $type = new HavingQueryType();
        $type->setOptions($options);

        return $type;
    }
}
