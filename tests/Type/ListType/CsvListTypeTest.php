<?php

namespace Povs\ListerBundle\Type\ListType;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\View\ListView;
use Povs\ListerBundle\View\RowView;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class CsvListTypeTest extends TestCase
{
    public function testGetLength(): void
    {
        $type = $this->getType(['length' => 100]);
        $this->assertEquals(100, $type->getLength(20));
    }

    public function testGetCurrentPage(): void
    {
        $type = $this->getType([]);
        $this->assertEquals(1, $type->getCurrentPage(20));
    }

    public function testGenerateResponse(): void
    {
        $listViewMock = $this->getListViewMock([['bar11', 'bar12'], ['bar21', 'bar22']], ['label1', 'label2']);
        $type = $this->getType(['file_name' => 'file', 'delimiter' => ',', 'limit' => 0]);
        $response = $type->generateResponse($listViewMock, []);
        ob_start();
        $response->sendContent();
        $result = ob_get_clean();
        $expected = [
            'label1,label2',
            'bar11,bar12',
            'bar21,bar22',
            ''
        ];
        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertEquals('text/csv; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename="file.csv"', $response->headers->get('Content-Disposition'));
        $this->assertEquals($expected, explode("\n", $result));
    }

    public function testGenerateData(): void
    {
        $listViewMock = $this->createMock(ListView::class);
        $type = $this->getType([]);
        $data = $type->generateData($listViewMock, []);
        $this->assertNull($data);
    }

    public function testLimit(): void
    {
        $listViewMock = $this->getListViewMock([['bar11', 'bar12'], ['bar21', 'bar22']], ['label1', 'label2']);
        $type = $this->getType(['file_name' => 'file', 'delimiter' => ',', 'limit' => 1]);
        $response = $type->generateResponse($listViewMock, []);
        ob_start();
        $response->sendContent();
        $result = ob_get_clean();
        $expected = [
            'label1,label2',
            'bar11,bar12',
            ''
        ];
        $this->assertEquals($expected, explode("\n", $result));
    }

    public function testConfigureSettings(): void
    {
        $resolver = new OptionsResolver();
        $type = $this->getType([]);
        $type->configureSettings($resolver);
        $res = $resolver->resolve(['file_name' => 'foo', 'length' => 1000]);
        $expected = [
            'length' => 1000,
            'file_name' => 'foo',
            'delimiter' => ',',
            'limit' => 0,
        ];
        $this->assertEquals($expected, $res);
    }

    /**
     * @param array $config
     *
     * @return CsvListType
     */
    private function getType(array $config): CsvListType
    {
        $type = new CsvListType();
        $type->setConfig($config);

        return $type;
    }

    /**
     * @param array $bodyRows
     * @param array $headerRow
     *
     * @return MockObject|ListView
     */
    private function getListViewMock(array $bodyRows, array $headerRow)
    {
        $listViewMock = $this->createMock(ListView::class);
        $bRows = [];

        foreach ($bodyRows as $row) {
            $rowMock = $this->createMock(RowView::class);
            $rowMock
                ->method('getValue')
                ->willReturn($row);
            $bRows[] = $rowMock;
        }

        $headerRowMock = $this->createMock(RowView::class);
        $headerRowMock->expects($this->once())
            ->method('getValue')
            ->willReturn($headerRow);

        $listViewMock->expects($this->once())
            ->method('getHeaderRow')
            ->willReturn($headerRowMock);
        $listViewMock->expects($this->once())
            ->method('getBodyRows')
            ->with(false)
            ->willReturn($bRows);

        return $listViewMock;
    }
}
