<?php

namespace Povs\ListerBundle\View;

use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\Mapper\ListField;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class FieldViewTest extends TestCase
{
    private static $defaultOptions = ['view_options', [], []];

    private $listFieldMock;

    public function setUp()
    {
        $this->listFieldMock = $this->createMock(ListField::class);
    }

    public function testGetRow(): void
    {
        $rowViewMock = $this->createMock(RowView::class);
        $view = $this->getFieldView();
        $view->init($rowViewMock, 'foo');
        $this->assertEquals($rowViewMock, $view->getRow());
    }

    public function testGetId(): void
    {
        $this->listFieldMock->expects($this->once())
            ->method('getId')
            ->willReturn('foo');
        $view = $this->getFieldView();

        $this->assertEquals('foo', $view->getId());
    }

    public function testGetValue(): void
    {
        $rowViewMock = $this->createMock(RowView::class);
        $view = $this->getFieldView();
        $view->init($rowViewMock, 'foo');
        $this->assertEquals('foo', $view->getValue());
    }

    public function testIsSortable(): void
    {
        $view = $this->getFieldView([
           self::$defaultOptions,
           ['sortable', null, true]
        ]);

        $this->assertTrue($view->isSortable());
    }

    public function testGetSort(): void
    {
        $view = $this->getFieldView([
            self::$defaultOptions,
            ['sort_value', null, 'DESC']
        ]);

        $this->assertEquals('DESC', $view->getSort());
    }

    public function testGetLabel(): void
    {
        $view = $this->getFieldView([
            self::$defaultOptions,
            ['label', null, 'foo']
        ]);

        $this->assertEquals('foo', $view->getLabel());
    }

    public function testGetListField(): void
    {
        $view = $this->getFieldView();
        $this->assertEquals($this->listFieldMock, $view->getListField());
    }

    public function testInit(): void
    {
        $rowViewMock = $this->createMock(RowView::class);
        $view = $this->getFieldView();
        $view->init($rowViewMock, 'foo');
        $this->assertEquals($rowViewMock, $view->getRow());
        $this->assertEquals('foo', $view->getValue());
    }

    /**
     * @param array|null $options
     * @return FieldView
     */
    private function getFieldView(?array $options = null): FieldView
    {
        if (!$options) {
            $options = [self::$defaultOptions];
        }

        $this->listFieldMock->expects($this->exactly(count($options)))
            ->method('getOption')
            ->willReturnMap($options);

        return new FieldView($this->listFieldMock);
    }
}
