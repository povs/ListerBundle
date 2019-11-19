<?php
namespace Povs\ListerBundle\View;

use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\Service\Paginator;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class PagerViewTest extends TestCase
{
    private $paginator;

    public function setUp()
    {
        $this->paginator = $this->createMock(Paginator::class);
    }

    public function testGetCurrentPage(): void
    {
        $view = $this->getPagerView(1, 20);
        $this->assertEquals(1, $view->getCurrentPage());
    }

    public function testGetTotal(): void
    {
        $view = $this->getPagerView(1, 20, 1000);
        $this->assertEquals(1000, $view->getTotal());
    }

    public function testGetLength(): void
    {
        $view = $this->getPagerView(1, 20);
        $this->assertEquals(20, $view->getLength());
    }

    public function testGetFirstResultWithPage(): void
    {
        $view = $this->getPagerView(1, 20);
        $this->assertEquals(81, $view->getFirstResult(5));
    }

    public function testGetFirstResultWithoutPage(): void
    {
        $view = $this->getPagerView(2, 20);
        $this->assertEquals(21, $view->getFirstResult());
    }

    public function testGetLastResultWithPageWithTotalGreaterThanResult(): void
    {
        $view = $this->getPagerView(1, 20, 100);
        $this->assertEquals(100, $view->getLastResult(5));
    }

    public function testGetLastResultWithPageWithTotalLessThanResult(): void
    {
        $view = $this->getPagerView(1, 20, 50);
        $this->assertEquals(50, $view->getLastResult(5));
    }

    public function testGetLastResultWithoutPage(): void
    {
        $view = $this->getPagerView(2, 20, 100);
        $this->assertEquals(40, $view->getLastResult());
    }

    public function testGetLastPage(): void
    {
        $view = $this->getPagerView(2, 40, 100);
        $this->assertEquals(3, $view->getLastPage());
    }

    public function testGetPrevPageValid(): void
    {
        $view = $this->getPagerView(3, 40, 100);
        $this->assertEquals(2, $view->getPrevPage());
    }

    public function testGetPrevPageNotValid(): void
    {
        $view = $this->getPagerView(1, 40, null);
        $this->assertNull($view->getPrevPage());
    }

    public function testGetNextPageValid(): void
    {
        $view = $this->getPagerView(2, 40, 100);
        $this->assertEquals(3, $view->getNextPage());
    }

    public function testGetNextPageNotValid(): void
    {
        $view = $this->getPagerView(3, 40, 100);
        $this->assertNull($view->getNextPage());
    }

    public function testValidatePageValid(): void
    {
        $view = $this->getPagerView(3, 40, 100);
        $this->assertTrue($view->validatePage(2));
    }

    public function testValidatePageNotValid(): void
    {
        $view = $this->getPagerView(3, 40, 100);
        $this->assertFalse($view->validatePage(4));
    }

    public function testIteratePageValid(): void
    {
        $this->paginator->expects($this->exactly(2))
            ->method('getCount')
            ->willReturn(100);
        $this->paginator->expects($this->exactly(2))
            ->method('getData')
            ->willReturnMap([
                [40, 40, ['foo' => 'bar']],
                [80, 40, ['foo1' => 'bar1']]
            ]);

        $view = $this->getPagerView(2, 40);
        $view->iteratePage(3);
        $this->assertEquals(3, $view->getCurrentPage());
        $this->assertEquals(['foo1' => 'bar1'], $view->getData());
    }

    public function testIteratePageNotValid(): void
    {
        $view = $this->getPagerView(2, 40, 100);
        $this->assertFalse($view->iteratePage(4));
    }

    public function testIterateNextPageValid(): void
    {
        $this->paginator->expects($this->exactly(2))
            ->method('getCount')
            ->willReturn(100);
        $this->paginator->expects($this->exactly(2))
            ->method('getData')
            ->willReturnMap([
                [0, 40, ['foo' => 'bar']],
                [40, 40, ['foo1' => 'bar1']]
            ]);

        $view = $this->getPagerView(1, 40);
        $view->iterateNextPage();
        $this->assertEquals(2, $view->getCurrentPage());
        $this->assertEquals(['foo1' => 'bar1'], $view->getData());
    }

    public function testIterateNextPageNotValid(): void
    {
        $view = $this->getPagerView(3, 40, 100);
        $this->assertFalse($view->iterateNextPage());
    }

    public function testGetData(): void
    {
        $this->paginator->expects($this->once())
            ->method('getData')
            ->with(80, 40)
            ->willReturn(['foo' => 'bar']);

        $view = $this->getPagerView(3, 40, 100);
        $this->assertEquals(['foo' => 'bar'], $view->getData());
    }

    /**
     * @dataProvider pagesProvider
     * @param int   $page
     * @param int   $length
     * @param int   $perPage
     * @param int   $pagesLength
     * @param array $expectedResult
     */
    public function testGetPages(int $page, int $length, int $perPage, int $pagesLength, array $expectedResult): void
    {
        $view = $this->getPagerView($page, $length, $perPage);
        $res = $view->getPages($pagesLength);

        $this->assertEquals($expectedResult, $res);
    }

    public function pagesProvider(): array
    {
        return [
            [1, 20, 100, 1, $this->getPages([1, 2, 3, 4, 5], 1)],
            [5, 20, 1000, 2, $this->getPages([1, 2, 3, 4, 5, 6, 7, null, 50], 5)],
            [1, 20, 0, 1, []],
            [10, 20, 1000, 2, $this->getPages([1, null, 8, 9, 10, 11, 12, null, 50], 10)],
            [10, 20, 1000, 1, $this->getPages([1, null, 9, 10, 11, null, 50], 10)],
            [48, 20, 1000, 1, $this->getPages([1, null, 46, 47, 48, 49, 50], 48)],
            [50, 20, 1000, 2, $this->getPages([1, null, 44, 45, 46, 47, 48, 49, 50], 50)],
        ];
    }

    /**
     * @param array $pages
     * @param int   $activePage
     *
     * @return array
     */
    private function getPages(array $pages, int $activePage): array
    {
        $p = [];

        foreach ($pages as $page) {
            $p[] = [
                'page' => $page,
                'active' => $activePage === $page
            ];
        }

        return $p;
    }

    /**
     * @param int        $currentPage
     * @param int        $perPage
     * @param int|null   $total
     * @param array|null $data
     *
     * @return PagerView
     */
    private function getPagerView(int $currentPage, int $perPage, ?int $total = null, ?array $data = null): PagerView
    {
        if (null !== $total) {
            $this->paginator->expects($this->once())
                ->method('getCount')
                ->willReturn($total);
        }

        if (null !== $data) {
            $this->paginator->expects($this->once())
                ->method('getData')
                ->willReturn($data);
        }

        return new PagerView($this->paginator, $currentPage, $perPage);
    }
}