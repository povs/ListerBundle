<?php
namespace Povs\ListerBundle\Factory;

use Doctrine\ORM\QueryBuilder;
use Povs\ListerBundle\Service\RequestHandler;
use Povs\ListerBundle\Service\ValueAccessor;
use Povs\ListerBundle\Mapper\ListMapper;
use Povs\ListerBundle\View\FieldView;
use Povs\ListerBundle\View\ListView;
use Povs\ListerBundle\View\PagerView;
use Povs\ListerBundle\View\RouterView;
use Povs\ListerBundle\View\RowView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ViewFactory
{
    /**
     * @var ValueAccessor
     */
    private $valueAccessor;

    /**
     * @var RequestHandler
     */
    private $requestHandler;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var PaginatorFactory
     */
    private $paginatorFactory;

    /**
     * ViewFactory constructor.
     *
     * @param ValueAccessor    $valueAccessor
     * @param RequestHandler   $listRequestHandler
     * @param RouterInterface  $router
     * @param PaginatorFactory $paginatorFactory
     */
    public function __construct(
        ValueAccessor $valueAccessor,
        RequestHandler $listRequestHandler,
        RouterInterface $router,
        PaginatorFactory $paginatorFactory
    ) {
        $this->valueAccessor = $valueAccessor;
        $this->requestHandler = $listRequestHandler;
        $this->router = $router;
        $this->paginatorFactory = $paginatorFactory;
    }

    /**
     * @param ListMapper    $listMapper
     * @param FormInterface $form
     * @param QueryBuilder  $queryBuilder
     * @param int           $resultsPerPage
     * @param int           $currentPage
     *
     * @return ListView
     */
    public function createView(
        ListMapper $listMapper,
        FormInterface $form,
        QueryBuilder $queryBuilder,
        int $resultsPerPage,
        int $currentPage
    ): ListView {
        $paginator = $this->paginatorFactory->createPaginator($queryBuilder);
        $pagerView = new PagerView($paginator, $currentPage, $resultsPerPage);
        $headerRow = new RowView($this->valueAccessor);
        $bodyRow = new RowView($this->valueAccessor);
        $routerView = new RouterView($this->requestHandler, $this->router);
        $fieldViews = [];

        foreach ($listMapper->getFields() as $field) {
            $fieldViews[] = new FieldView($field);
        }

        $listView = new ListView(
            $pagerView,
            $form->createView(),
            $routerView,
            $headerRow,
            $bodyRow,
            $fieldViews
        );

        return $listView;
    }
}