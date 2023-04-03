<?php

namespace Povs\ListerBundle\Service;

use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\Mapper\FilterMapper;
use Povs\ListerBundle\Mapper\ListField;
use Povs\ListerBundle\Mapper\ListMapper;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListValueTest extends TestCase
{
    private $listMapperMock;
    private $filterMapperMock;

    public function setUp(): void
    {
        $this->listMapperMock = $this->createMock(ListMapper::class);
        $this->filterMapperMock = $this->createMock(FilterMapper::class);
    }

    public function testHasFilterField(): void
    {
        $this->filterMapperMock->expects($this->once())
            ->method('has')
            ->with('foo')
            ->willReturn(true);

        $this->assertTrue($this->getValueFacade()->hasFilterField('foo'));
    }

    public function testHasListField(): void
    {
        $this->listMapperMock->expects($this->once())
            ->method('has')
            ->with('foo')
            ->willReturn(true);

        $this->assertTrue($this->getValueFacade()->hasListField('foo'));
    }

    public function testGetFilterValue(): void
    {
        $this->filterMapperMock->expects($this->once())
            ->method('getValue')
            ->with('foo')
            ->willReturn('bar');

        $this->assertEquals('bar', $this->getValueFacade()->getFilterValue('foo'));
    }

    public function testGetSortValue(): void
    {
        $fieldMock = $this->createMock(ListField::class);
        $fieldMock->expects($this->once())
            ->method('getOption')
            ->with('sort_value', null)
            ->willReturn('ASC');
        $this->listMapperMock->expects($this->once())
            ->method('get')
            ->with('foo')
            ->willReturn($fieldMock);

        $this->assertEquals('ASC', $this->getValueFacade()->getSortValue('foo'));
    }

    /**
     * @return ListValue
     */
    private function getValueFacade(): ListValue
    {
        return new ListValue(
            $this->listMapperMock,
            $this->filterMapperMock
        );
    }
}
