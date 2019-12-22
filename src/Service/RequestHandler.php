<?php
namespace Povs\ListerBundle\Service;

use Povs\ListerBundle\Mapper\FilterMapper;
use Povs\ListerBundle\Mapper\ListField;
use Povs\ListerBundle\Mapper\ListMapper;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class RequestHandler
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var ConfigurationResolver
     */
    private $configuration;

    /**
     * ListRequestHandler constructor.
     *
     * @param RequestStack          $requestStack
     * @param ConfigurationResolver $configurationResolver
     */
    public function __construct(RequestStack $requestStack, ConfigurationResolver $configurationResolver)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->configuration = $configurationResolver;
    }

    /**
     * @param ListMapper    $listMapper
     * @param FilterMapper  $filterMapper
     * @param FormInterface $form
     */
    public function handleRequest(ListMapper $listMapper, FilterMapper $filterMapper, FormInterface $form): void
    {
        $this->handleListRequest($listMapper);
        $this->handleFilterRequest($filterMapper, $form);
    }

    /**
     * @return int|null
     */
    public function getCurrentPage(): ?int
    {
        $currentPage = $this->getValue('page');

        return $currentPage ? (int) $currentPage : null;
    }

    /**
     * @return int|null
     */
    public function getLength(): ?int
    {
        $perPage = $this->getValue('length');

        return $perPage ? (int) $perPage : null;
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function getValue(string $name)
    {
        $name = $this->getName($name);

        return $this->request->query->get($name);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getName(string $name): string
    {
        return $this->configuration->getRequestConfiguration($name);
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @param FilterMapper  $filterMapper
     * @param FormInterface $form
     */
    private function handleFilterRequest(FilterMapper $filterMapper, FormInterface $form): void
    {
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            foreach ($data as $name => $datum) {
                $field = $filterMapper->get($name);
                $field->setValue($datum);
            }
        }
    }

    /**
     * @param ListMapper $listMapper
     */
    private function handleListRequest(ListMapper $listMapper): void
    {
        $sort = $this->getValue('sort') ?? [];

        foreach ($sort as $id => $direction) {
            $direction = strtoupper($direction);

            if (!in_array($direction, [ListField::SORT_DESC, ListField::SORT_ASC], true)) {
                continue;
            }

            $field = $listMapper->get($id);
            $field->setOption(ListField::OPTION_SORT_VALUE, $direction);
        }
    }
}