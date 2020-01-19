<?php

namespace Povs\ListerBundle\View;

use Doctrine\ORM\QueryBuilder;
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
        return $this->getRowView('body', ['foo' => 'bar', 'list_identifier' => '100']);
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
        $this->assertEquals(['id' => 'field'], $rowView->getValue());
    }

    /**
     * @depends testInitBody
     * @param RowView $rowView
     */
    public function testGetIdBody(RowView $rowView): void
    {
        $this->assertEquals(100, $rowView->getId());
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
        $this->assertEquals(['id' => 'header'], $rowView->getValue());
    }

    /**
     * @depends testInitHeader
     * @param RowView $rowView
     */
    public function testGetIdHeader(RowView $rowView): void
    {
        $this->assertNull($rowView->getId());
    }

    public function testGetList(): void
    {
        $listViewMock = $this->createMock(ListView::class);
        $view = $this->getRowView('body', ['list_identifier' => '100'], $listViewMock);

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
        $listFieldMock = $this->createMock(ListField::class);
        $listFieldMock->expects($this->once())
            ->method('getId')
            ->willReturn('id');
        $fieldViewMock->method('getListField')
            ->willReturn($listFieldMock);

        if ($type === 'body') {
            $queryBuilderMock = $this->createMock(QueryBuilder::class);
            $valueAccessor->expects($this->once())
                ->method('getFieldValue')
                ->with($fieldViewMock, ['normalized' => 'data'])
                ->willReturn('field');
            $valueAccessor->expects($this->once())
                ->method('normalizeData')
                ->with($data, $queryBuilderMock)
                ->willReturn(['normalized' => 'data']);
            $fieldViewMock->method('getValue')
                ->willReturn('field');
        } else {
            $queryBuilderMock = null;
            $valueAccessor->expects($this->once())
                ->method('getHeaderValue')
                ->with($fieldViewMock)
                ->willReturn('header');
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

        $rowView = new RowView($valueAccessor, $queryBuilderMock);
        $rowView->init($listViewMock, $type, $data);

        return $rowView;
    }
}
