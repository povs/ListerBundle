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

        $type = $this->getType(['foo'], 'foo', $options);
        $type->filter($queryBuilderMock, 'bar', 'value');
    }

    /**
     * @return array
     */
    public function filterProvider(): array
    {
        return [
            [['boolean' => true, 'expand' => true], 'MATCH (foo) HAVING (:bar boolean expand)'],
            [['boolean' => true, 'expand' => false], 'MATCH (foo) HAVING (:bar boolean)'],
            [['boolean' => false, 'expand' => true], 'MATCH (foo) HAVING (:bar expand)'],
            [['boolean' => false, 'expand' => false], 'MATCH (foo) HAVING (:bar)'],
        ];
    }

    public function testConfigureOptions(): void
    {
        $optionResolver = new OptionsResolver();
        $type = $this->getType([], '', []);
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
        $type = $this->getType([], '', []);
        $type->configureOptions($optionResolver);

        $this->assertEquals(['relevance' => 0, 'boolean' => false, 'expand' => false], $optionResolver->resolve());
    }

    /**
     * @param array  $paths
     * @param string $path
     * @param array  $options
     *
     * @return MatchQueryType
     */
    private function getType(array $paths, string $path, array $options): MatchQueryType
    {
        $type = new MatchQueryType();
        $type->setPaths($paths);
        $type->setPath($path);
        $type->setOptions($options);

        return $type;
    }
}