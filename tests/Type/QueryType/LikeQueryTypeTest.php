<?php
namespace Povs\ListerBundle\Type\QueryType;

use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class LikeQueryTypeTest extends TestCase
{
    public function testFilter(): void
    {
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->expects($this->once())
            ->method('andWhere')
            ->with($this->callback(static function(Comparison $subject) {
                return $subject->getLeftExpr() === 'foo' &&
                    $subject->getOperator() === 'LIKE' &&
                    $subject->getRightExpr() === ':bar';
            }))
            ->willReturnSelf();
        $queryBuilderMock->expects($this->once())
            ->method('setParameter')
            ->with(':bar', '%value%');

        $type = $this->getType(['foo'], 'foo', ['type' => 'wildcard']);
        $type->filter($queryBuilderMock, 'bar', 'value');
    }

    public function testConfigureOptions(): void
    {
        $optionResolver = new OptionsResolver();
        $type = $this->getType([], '', []);
        $type->configureOptions($optionResolver);

        $this->assertEquals(['type' => 'wildcard'], $optionResolver->resolve(['type' => 'wildcard']));
    }

    public function testConfigureOptionsDefault(): void
    {
        $optionResolver = new OptionsResolver();
        $type = $this->getType([], '', []);
        $type->configureOptions($optionResolver);

        $this->assertEquals(['type' => 'default'], $optionResolver->resolve());
    }

    /**
     * @param array  $paths
     * @param string $path
     * @param array  $options
     *
     * @return LikeQueryType
     */
    private function getType(array $paths, string $path, array $options): LikeQueryType
    {
        $type = new LikeQueryType();
        $type->setPaths($paths);
        $type->setPath($path);
        $type->setOptions($options);

        return $type;
    }
}