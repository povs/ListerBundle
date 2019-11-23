<?php
namespace Povs\ListerBundle\Type\ListType;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\View\ListView;
use Povs\ListerBundle\View\PagerView;
use Povs\ListerBundle\View\RowView;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ArrayListTypeTest extends TestCase
{
    public function testGetLengthNotPaged(): void
    {
        $type = $this->getType(['paged' => false, 'length' => 100]);
        $this->assertEquals(100, $type->getLength(20));
    }

    public function testGetLengthPagedWithoutLength(): void
    {
        $type = $this->getType(['paged' => true, 'length' => 100]);
        $this->assertEquals(100, $type->getLength(null));
    }

    public function testGetLengthPagedWithLength(): void
    {
        $type = $this->getType(['paged' => true, 'length' => 100]);
        $this->assertEquals(50, $type->getLength(50));
    }

    public function testGetCurrentPageNotPaged(): void
    {
        $type = $this->getType(['paged' => false]);
        $this->assertEquals(1, $type->getCurrentPage(5));
    }

    public function testGetCurrentPagePagedWithoutPage(): void
    {
        $type = $this->getType(['paged' => true]);
        $this->assertEquals(1, $type->getCurrentPage(null));
    }

    public function testGetCurrentPagePagedWithPage(): void
    {
        $type = $this->getType(['paged' => true]);
        $this->assertEquals(5, $type->getCurrentPage(5));
    }

    public function testGenerateResponse(): void
    {
        $listViewMock = $this->getListViewMock([
            [['foo11' => 'bar11'], ['foo12' => 'bar12']],
            [['foo21' => 'bar21'], ['foo22' => 'bar22']]
        ]);

        $type = $this->getType(['paged' => false, 'limit' => 0]);
        $response = $type->generateResponse($listViewMock, []);
        $expected = '{"data":[[{"foo11":"bar11"},{"foo12":"bar12"}],[{"foo21":"bar21"},{"foo22":"bar22"}]],"total":2}';
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($expected, $response->getContent());
    }

    public function testGenerateData(): void
    {
        $listViewMock = $this->getListViewMock([
            [['foo11' => 'bar11'], ['foo12' => 'bar12']],
            [['foo21' => 'bar21'], ['foo22' => 'bar22']]
        ]);

        $type = $this->getType(['paged' => false, 'limit' => 0]);
        $data = $type->generateData($listViewMock, []);
        $expected = [
            'data' => [
                [['foo11' => 'bar11'], ['foo12' => 'bar12']],
                [['foo21' => 'bar21'], ['foo22' => 'bar22']]
            ],
            'total' => 2
        ];
        $this->assertEquals($expected, $data);
    }

    public function testLimit(): void
    {
        $listViewMock = $this->getListViewMock([
            [['foo11' => 'bar11'], ['foo12' => 'bar12']],
            [['foo21' => 'bar21'], ['foo22' => 'bar22']]
        ]);

        $type = $this->getType(['paged' => false, 'limit' => 1]);
        $data = $type->generateData($listViewMock, []);
        $expected = [
            'data' => [
                [['foo11' => 'bar11'], ['foo12' => 'bar12']]
            ],
            'total' => 2
        ];
        $this->assertEquals($expected, $data);
    }

    public function testConfigureSettings(): void
    {
        $resolver = new OptionsResolver();
        $type = $this->getType([]);
        $type->configureSettings($resolver);
        $result = $resolver->resolve(['length' => 1000]);
        $expected = [
            'length' => 1000,
            'limit' => 0,
            'paged' => true
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * @param array $config
     *
     * @return ArrayListType
     */
    private function getType(array $config): ArrayListType
    {
        $type = new ArrayListType();
        $type->setConfig($config);

        return $type;
    }

    /**
     * @param array $rows
     *
     * @return MockObject|ListView
     */
    private function getListViewMock(array $rows)
    {
        $listViewMock = $this->createMock(ListView::class);
        $pagedViewMock = $this->createMock(PagerView::class);
        $bodyRows = [];

        foreach ($rows as $row) {
            $bodyRowMock = $this->createMock(RowView::class);
            $bodyRowMock
                ->method('getLabeledValue')
                ->willReturn($row);
            $bodyRows[] = $bodyRowMock;
        }

        $listViewMock->expects($this->once())
            ->method('getBodyRows')
            ->with(false)
            ->willReturn($bodyRows);
        $listViewMock->expects($this->once())
            ->method('getPager')
            ->willReturn($pagedViewMock);
        $pagedViewMock->expects($this->once())
            ->method('getTotal')
            ->willReturn(count($rows));

        return $listViewMock;
    }
}