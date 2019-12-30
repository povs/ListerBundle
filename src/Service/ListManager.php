<?php
namespace Povs\ListerBundle\Service;

use Povs\ListerBundle\Definition\ListInterface;
use Povs\ListerBundle\DependencyInjection\Locator\ListLocator;
use Povs\ListerBundle\Exception\ListException;
use Povs\ListerBundle\Factory\MapperFactory;
use Povs\ListerBundle\Factory\ListValueFactory;
use Povs\ListerBundle\Factory\ViewFactory;
use Povs\ListerBundle\View\ListView;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListManager
{
    /**
     * @var ListTypeResolver
     */
    private $typeResolver;

    /**
     * @var ConfigurationResolver
     */
    private $configurationResolver;

    /**
     * @var RequestHandler
     */
    private $requestHandler;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var ListQueryBuilder
     */
    private $queryBuilder;

    /**
     * @var ViewFactory
     */
    private $viewFactory;

    /**
     * @var MapperFactory
     */
    private $mapperFactory;

    /**
     * @var ListValueFactory
     */
    private $valueFactory;

    /**
     * @var ListLocator
     */
    private $listLocator;

    /**
     * Is set when called buildList method
     *
     * @var ListView|null
     */
    private $listView;

    /**
     * ListManager constructor.
     *
     * @param ListTypeResolver      $typeResolver
     * @param ConfigurationResolver $configurationResolver
     * @param RequestHandler        $requestHandler
     * @param FilterBuilder         $filterBuilder
     * @param ListQueryBuilder      $queryBuilder
     * @param ViewFactory           $viewFactory
     * @param MapperFactory         $mapperFactory
     * @param ListValueFactory      $valueFactory
     * @param ListLocator           $listLocator
     */
    public function __construct(
        ListTypeResolver $typeResolver,
        ConfigurationResolver $configurationResolver,
        RequestHandler $requestHandler,
        FilterBuilder $filterBuilder,
        ListQueryBuilder $queryBuilder,
        ViewFactory $viewFactory,
        MapperFactory $mapperFactory,
        ListValueFactory $valueFactory,
        ListLocator $listLocator
    ) {
        $this->typeResolver = $typeResolver;
        $this->configurationResolver = $configurationResolver;
        $this->requestHandler = $requestHandler;
        $this->filterBuilder = $filterBuilder;
        $this->queryBuilder = $queryBuilder;
        $this->viewFactory = $viewFactory;
        $this->mapperFactory = $mapperFactory;
        $this->valueFactory = $valueFactory;
        $this->listLocator = $listLocator;
    }

    /**
     * @param string $list
     * @param string $type
     * @param array  $parameters
     */
    public function buildList(string $list, string $type, array $parameters = []): void
    {
        $list = $this->generateList($list, $parameters);
        $this->typeResolver->resolveType($type);
        $listMapper = $this->mapperFactory->createListMapper($list, $this->typeResolver->getTypeName());
        $filterMapper = $this->mapperFactory->createFilterMapper($list);
        $filterForm = $this->filterBuilder->buildFilterForm($filterMapper);
        $this->requestHandler->handleRequest($listMapper, $filterMapper, $filterForm);
        $listValue = $this->valueFactory->createListValue($listMapper, $filterMapper);
        $joinMapper = $this->mapperFactory->createJoinMapper($list, $listMapper, $filterMapper, $listValue);
        $queryBuilder = $this->queryBuilder->buildQuery($list, $joinMapper, $listMapper, $filterMapper, $listValue);
        $lazyQueryBuilder = $this->queryBuilder->buildLazyQuery($list, $joinMapper, $listMapper);
        $this->listView = $this->viewFactory->createView(
            $listMapper,
            $filterForm,
            $queryBuilder,
            $lazyQueryBuilder,
            $this->typeResolver->getPerPage(),
            $this->typeResolver->getCurrentPage()
        );
    }

    /**
     * @param array $options
     *
     * @return Response
     */
    public function getResponse(array $options = []): Response
    {
        if (!$this->listView) {
            throw ListException::listNotBuilt();
        }

        return $this->typeResolver->getResponse($this->listView, $options);
    }

    /**
     * @param array $options
     *
     * @return mixed
     */
    public function getData(array $options = [])
    {
        if (!$this->listView) {
            throw ListException::listNotBuilt();
        }

        return $this->typeResolver->getData($this->listView, $options);
    }

    /**
     * @param string $list
     * @param array  $parameters
     *
     * @return ListInterface
     */
    private function generateList(string $list, array $parameters = []): ListInterface
    {
        $list = $this->listLocator->get($list);
        $list->setParameters($parameters);
        $this->configurationResolver->resolve($list);

        return $list;
    }
}