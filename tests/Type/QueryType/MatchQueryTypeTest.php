<?php
namespace Povs\ListerBundle\Type\QueryType;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class MatchQueryTypeTest extends TestCase
{
    /**
     * @dataProvider filterProvider
     * @param array  $options
     * @param string $clause
     */
    public function testFilter(array $options, string $clause): void
    {
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->expects($this->once())
            ->method('andWhere')
            ->with($clause)
            ->willReturnSelf();
        $queryBuilderMock->expects($this->once())
            ->method('setParameter')
            ->with(':bar', 'value');

        $type = $this->getType($options);
        $type->filter($queryBuilderMock, ['foo', 'bar'], 'bar', 'value');
    }

    /**
     * @return array
     */
    public function filterProvider(): array
    {
        return [
            [['boolean' => true, 'expand' => true, 'relevance' => 1], 'MATCH (foo,bar) HAVING (:bar boolean expand) > 1'],
            [['boolean' => true, 'expand' => false, 'relevance' => 0], 'MATCH (foo,bar) HAVING (:bar boolean) > 0'],
            [['boolean' => false, 'expand' => true, 'relevance' => 0.123], 'MATCH (foo,bar) HAVING (:bar expand) > 0.123'],
            [['boolean' => false, 'expand' => false, 'relevance' => 1.123], 'MATCH (foo,bar) HAVING (:bar) > 1.123'],
        ];
    }

    public function testConfigureOptions(): void
    {
        $optionResolver = new OptionsResolver();
        $type = $this->getType([]);
        $type->configureOptions($optionResolver);
        $options = [
            'relevance' => 0.1561,
            'boolean' => true,
            'expand' => true
        ];

        $this->assertEquals($options, $optionResolver->resolve($options));
    }

    public function testConfigureOptionsDefault(): void
    {
        $optionResolver = new OptionsResolver();
        $type = $this->getType([]);
        $type->configureOptions($optionResolver);

        $this->assertEquals(['relevance' => 0, 'boolean' => false, 'expand' => false], $optionResolver->resolve());
    }

    public function testHasAggregation(): void
    {
        $this->assertFalse($this->getType([])->hasAggregation());
    }

    /**
     * @param array $options
     *
     * @return MatchQueryType
     */
    private function getType( array $options): MatchQueryType
    {
        $type = new MatchQueryType();
        $type->setOptions($options);

        return $type;
    }
}