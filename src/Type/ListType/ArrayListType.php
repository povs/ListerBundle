<?php
namespace Povs\ListerBundle\Type\ListType;

use Povs\ListerBundle\View\ListView;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ArrayListType extends AbstractListType
{
    /**
     * @inheritDoc
     */
    public function getLength(?int $length): int
    {
        if (false === $this->config['paged'] || null === $length) {
            $length = $this->config['length'];
        }

        return $length;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentPage(?int $currentPage): int
    {
        if (false === $this->config['paged'] || null === $currentPage) {
            $currentPage = 1;
        }

        return $currentPage;
    }

    /**
     * @inheritDoc
     */
    public function generateResponse(ListView $listView, array $options): Response
    {
        return new JsonResponse($this->buildData($listView));
    }

    /**
     * @inheritDoc
     */
    public function generateData(ListView $listView, array $options): array
    {
        return $this->buildData($listView);
    }

    /**
     * @inheritDoc
     */
    public function configureSettings(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefined(['length', 'limit', 'paged']);
        $optionsResolver->setRequired(['length', 'limit', 'paged']);
        $optionsResolver->setAllowedTypes('length', 'int');
        $optionsResolver->setAllowedTypes('limit', 'int');
        $optionsResolver->setAllowedTypes('paged', 'bool');
        $optionsResolver->setDefaults([
            'length' => 10000,
            'limit' => 0,
            'paged' => true
        ]);
    }

    /**
     * @param ListView $listView
     *
     * @return array
     */
    protected function buildData(ListView $listView): array
    {
        $batch = 0;
        $data = [
            'data' => [],
            'total' => $listView->getPager()->getTotal()
        ];

        foreach ($listView->getBodyRows($this->config['paged'], false) as $row) {
            $data['data'][] = $row->getLabeledValue();

            if (0 !== $this->config['limit'] && ++$batch === $this->config['limit']) {
                break;
            }
        }

        return $data;
    }
}
