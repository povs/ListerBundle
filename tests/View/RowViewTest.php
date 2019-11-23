<?php
namespace Povs\ListerBundle\View;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\Mapper\ListField;
use Povs\ListerBundle\Service\ValueAccessor;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class RowViewTest extends TestCase
{
    public function testInitBody(): RowView
    {
        return $this->getRowView('body', ['foo' => 'bar']);
    }

    public function testInitHeader(): RowView
    {
        return $this->getRowView('header', null);
    }

    /**
     * @depends testInitBody
     * @param RowView $rowView
     */
    public function testGetGetFields(RowView $rowView): void
    {
        $this->assertCount(1, $rowView->getFields());
        $this->assertInstanceOf(FieldView::class, $rowView->getFields()[0]);
    }

    /**
     * @depends testInitBody
     * @param RowView $rowView
     */
    public function testGetValueBody(RowView $rowView): void
    {
        $this->assertEquals(['field'], $rowView->getValue());
    }

    /**
     * @depends testInitBody
     * @param RowView $rowView
     */
    public function testGetLabeledValueBody(RowView $rowView): void
    {
        $this->assertEquals(['header' => 'field'], $rowView->getLabeledValue());
    }

    /**
     * @depends testInitHeader
     * @param RowView $rowView
     */
    public function testGetLabeledValueHeader(RowView $rowView): void
    {
        $this->assertEquals(['header' => 'header'], $rowView->getLabeledValue());
    }

    /**
     * @depends testInitHeader
     * @param RowView $rowView
     */
    public function testGetValueHeader(RowView $rowView): void
    {
        $this->assertEquals(['header'], $rowView->getValue());
    }

    public function testGetList(): void
    {
        $listViewMock = $this->createMock(ListView::class);
        $view = $this->getRowView('body', [], $listViewMock);

        $this->assertEquals($listViewMock, $view->getList());
    }

    /**
     * @param string     $type
     * @param array|null $data
     * @param MockObject $listViewMock
     *
     * @return RowView
     */
    private function getRowView(string $type, ?array $data, ?MockObject $listViewMock = null): RowView
    {
        $valueAccessor = $this->createMock(ValueAccessor::class);
        $listViewMock = $listViewMock ?: $this->createMock(ListView::class);
        $fieldViewMock = $this->createMock(FieldView::class);
        $listViewMock->expects($this->once())
            ->method('getFieldViews')
            ->willReturn([$fieldViewMock]);

        if ($type === 'body') {
            $valueAccessor->expects($this->once())
                ->method('getFieldValue')
                ->with($fieldViewMock, $data)
                ->willReturn('field');
            $fieldViewMock->method('getValue')
                ->willReturn('field');
        } else {
            $valueAccessor->expects($this->once())
                ->method('getHeaderValue')
                ->with($fieldViewMock)
                ->willReturn('header');
            $listFieldMock = $this->createMock(ListField::class);
            $fieldViewMock->expects($this->once())
                ->method('getListField')
                ->willReturn($listFieldMock);
            $listFieldMock->expects($this->once())
                ->method('setOption')
                ->with('label', 'header');
            $fieldViewMock->method('getValue')
                ->willReturn('header');
        }

        $fieldViewMock->method('getLabel')
            ->willReturn('header');

        $fieldViewMock->expects($this->once())
            ->method('init')
            ->with($this->isInstanceOf(RowView::class), $type === 'body' ? 'field' : 'header');

        $rowView = new RowView($valueAccessor);
        $rowView->init($listViewMock, $type, $data);

        return $rowView;
    }
}