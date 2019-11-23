<?php
namespace Povs\ListerBundle\Type\QueryType;

use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ComparisonQueryTypeTest extends TestCase
{
    public function testFilterSinglePath(): void
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

        $type = $this->getType(['type' => 'LIKE', 'wildcard' => 'wildcard']);
        $type->filter($queryBuilderMock, ['foo'], 'bar', 'value');
    }

    public function testFilterMultiplePaths(): void
    {
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->expects($this->once())
            ->method('andWhere')
            ->with($this->callback(static function(Comparison $subject) {
                return $subject->getLeftExpr() === 'CONCAT(foo,\'-\',bar)' &&
                    $subject->getOperator() === 'LIKE' &&
                    $subject->getRightExpr() === ':bar';
            }))
            ->willReturnSelf();
        $queryBuilderMock->expects($this->once())
            ->method('setParameter')
            ->with(':bar', '%value%');

        $type = $this->getType(['type' => 'LIKE', 'wildcard' => 'wildcard', 'delimiter' => '-']);
        $type->filter($queryBuilderMock, ['foo', 'bar'], 'bar', 'value');
    }

    public function testConfigureOptions(): void
    {
        $optionResolver = new OptionsResolver();
        $type = $this->getType([]);
        $type->configureOptions($optionResolver);
        $options = [
            'type' => '<',
            'wildcard' => 'wildcard_start',
            'delimiter' => '-'
        ];

        $this->assertEquals($options, $optionResolver->resolve($options));
    }

    public function testConfigureOptionsDefault(): void
    {
        $optionResolver = new OptionsResolver();
        $type = $this->getType([]);
        $type->configureOptions($optionResolver);
        $default = [
            'type' => '=',
            'wildcard' => 'no_wildcard',
            'delimiter' => ' '
        ];

        $this->assertEquals($default, $optionResolver->resolve());
    }

    /**
     * @param array $options
     *
     * @return ComparisonQueryType
     */
    private function getType(array $options): ComparisonQueryType
    {
        $type = new ComparisonQueryType();
        $type->setOptions($options);

        return $type;
    }
}