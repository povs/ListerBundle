<?php
namespace Povs\ListerBundle\Type\ListType;

use Povs\ListerBundle\View\ListView;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class CsvListType extends AbstractListType
{
    /**
     * @inheritDoc
     */
    public function getLength(?int $length): int
    {
        return $this->config['length'];
    }

    /**
     * @inheritDoc
     */
    public function getCurrentPage(?int $currentPage): int
    {
        return 1;
    }

    /**
     * @inheritDoc
     */
    public function generateResponse(ListView $listView, array $options): Response
    {
        $response = new StreamedResponse(function() use ($listView) {
            $this->buildCsv($listView);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set(
            'Content-Disposition',
            sprintf('attachment; filename="%s.csv"', $this->config['file_name'])
        );

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function generateData(ListView $listView, array $options)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function configureSettings(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefined(['length', 'file_name', 'delimiter', 'limit']);
        $optionsResolver->setRequired(['length', 'file_name', 'delimiter', 'limit']);
        $optionsResolver->setAllowedTypes('length', 'int');
        $optionsResolver->setAllowedTypes('file_name', 'string');
        $optionsResolver->setAllowedTypes('delimiter', 'string');
        $optionsResolver->setAllowedTypes('limit', 'int');
        $optionsResolver->setDefaults([
            'length' => 10000,
            'delimiter' => ',',
            'limit' => 0
        ]);
    }

    /**
     * @param ListView $listView
     */
    protected function buildCsv(ListView $listView): void
    {
        $csv = fopen('php://output', 'wb');
        $this->buildHeader($listView, $csv);
        $this->buildBody($listView, $csv);
    }

    /**
     * @param ListView $listView
     * @param resource $csv
     */
    protected function buildHeader(ListView $listView, $csv): void
    {
        $this->writeOutput($listView->getHeaderRow()->getValue(), $csv);
    }

    /**
     * @param ListView $listView
     * @param resource $csv
     */
    protected function buildBody(ListView $listView, $csv): void
    {
        $batch = 0;

        foreach ($listView->getBodyRows(false) as $row) {
            $this->writeOutput($row->getValue(), $csv);

            if (0 !== $this->config['limit'] && ++$batch === $this->config['limit']) {
                break;
            }
        }
    }

    /**
     * @param array    $row
     * @param resource $csv
     */
    protected function writeOutput(array $row, $csv): void
    {
        fputcsv($csv, $row, $this->config['delimiter']);
    }
}