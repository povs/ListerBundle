<?php

namespace Povs\ListerBundle\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListCompilerPassTest extends TestCase
{
    private $compilerPass;
    private $containerBuilder;

    public function setUp(): void
    {
        $this->compilerPass = new ListCompilerPass();
        $this->containerBuilder = $this->createMock(ContainerBuilder::class);
    }

    public function testProcess(): void
    {
        $locatorDefinitionMock = $this->createMock(Definition::class);
        $this->containerBuilder->expects($this->exactly(6))
            ->method('findTaggedServiceIds')
            ->withConsecutive(
                ['povs_lister.list', true],
                ['povs_lister.query_type', true],
                ['povs_lister.field_type', true],
                ['povs_lister.list_type', true],
                ['povs_lister.filter_type', true],
                ['povs_lister.selector_type', true]
            )
            ->willReturn([
                'id' => ['tag'],
            ]);
        $this->containerBuilder->expects($this->exactly(6))
            ->method('getDefinition')
            ->withConsecutive(
                ['.povs_lister.locator.list'],
                ['.povs_lister.locator.query_type'],
                ['.povs_lister.locator.field_type'],
                ['.povs_lister.locator.list_type'],
                ['.povs_lister.locator.filter_type'],
                ['.povs_lister.locator.selector_type']
            )
            ->willReturn($locatorDefinitionMock);
        $locatorDefinitionMock->expects($this->exactly(6))
            ->method('setArgument')
            ->with(0, $this->callback(static function ($arg) {
                $argument = $arg['id'];
                $value = $argument->getValues()[0];
                return count($arg) === 1 &&
                    $argument instanceof ServiceClosureArgument &&
                    $value instanceof Reference &&
                    (string) $value === 'id';
            }));

        $this->compilerPass->process($this->containerBuilder);
    }
}
