<?php
namespace Povs\ListerBundle\View;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormView;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListViewTest extends TestCase
{
    private $pagerViewMock;
    private $formViewMock;
    private $routerViewMock;
    private $headerRowMock;
    private $bodyRowMock;

    public function setUp()
    {
        $this->pagerViewMock = $this->createMock(PagerView::class);
        $this->formViewMock = $this->createMock(FormView::class);
        $this->routerViewMock = $this->createMock(RouterView::class);
        $this->headerRowMock = $this->createMock(RowView::class);
        $this->bodyRowMock = $this->createMock(RowView::class);
    }

    public function testGetHeaderRow(): void
    {
        $listView = $this->getListView();
        $this->assertEquals($this->headerRowMock, $listView->getHeaderRow());
    }

    public function testGetBodyRowsPaged(): void
    {
        $this->pagerViewMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                ['field1' => 'foo', 'field2' => 'bar'],
                ['field1' => 'foo', 'field2' => 'bar']
            ]);
        $this->bodyRowMock->expects($this->exactly(2))
            ->method('init')
            ->with(
                $this->isInstanceOf(ListView::class),
                'body',
                $this->equalTo(['field1' => 'foo', 'field2' => 'bar'])
            );
        $view = $this->getListView();
        $this->assertCount(2, $view->getBodyRows(true));
    }

    public function testGetBodyRowsNotPaged(): void
    {
        $firstPage = [
            ['field1' => 'foo', 'field2' => 'bar'],
            ['field1' => 'foo', 'field2' => 'bar']
        ];
        $secondPage = [
            ['field1' => 'foo', 'field2' => 'bar'],
            ['field1' => 'foo', 'field2' => 'bar']
        ];

        $this->pagerViewMock->expects($this->exactly(2))
            ->method('getData')
            ->willReturnOnConsecutiveCalls($firstPage, $secondPage);
        $this->pagerViewMock->expects($this->exactly(2))
            ->method('iterateNextPage')
            ->willReturnOnConsecutiveCalls(true, false);
        $this->bodyRowMock->expects($this->exactly(4))
            ->method('init')
            ->with(
                $this->isInstanceOf(ListView::class),
                'body',
                $this->equalTo(['field1' => 'foo', 'field2' => 'bar'])
            );
        $view = $this->getListView();
        $this->assertCount(4, $view->getBodyRows(false));
    }

    public function testGetPager(): void
    {
        $this->assertEquals($this->pagerViewMock, $this->getListView()->getPager());
    }

    public function testGetRouter(): void
    {
        $this->assertEquals($this->routerViewMock, $this->getListView()->getRouter());
    }

    public function testGetFilter(): void
    {
        $this->assertEquals($this->formViewMock, $this->getListView()->getFilter());
    }

    public function testGetFieldViews(): void
    {
        $fieldViews = ['view1', 'view2'];
        $this->assertEquals($fieldViews, $this->getListView($fieldViews)->getFieldViews());
    }

    /**
     * @param array $fieldViews
     *
     * @return ListView
     */
    private function getListView(array $fieldViews = []): ListView
    {
        $this->headerRowMock->expects($this->once())
            ->method('init')
            ->with($this->isInstanceOf(ListView::class), 'header', null);

        return new ListView(
            $this->pagerViewMock,
            $this->formViewMock,
            $this->routerViewMock,
            $this->headerRowMock,
            $this->bodyRowMock,
            $fieldViews
        );
    }
}