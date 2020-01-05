<?php
namespace Povs\ListerBundle\View;

use Generator;
use Symfony\Component\Form\FormView;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListView implements ViewInterface
{
    /**
     * @var PagerView
     */
    private $pagerView;

    /**
     * @var FormView
     */
    private $filterView;

    /**
     * @var RouterView
     */
    private $routerView;

    /**
     * @var FieldView[]
     */
    private $fieldViews;

    /**
     * @var RowView
     */
    private $bodyRow;

    /**
     * @var RowView
     */
    private $headerRow;

    /**
     * ListView constructor.
     *
     * @param PagerView  $pagerView
     * @param FormView   $formView
     * @param RouterView $routerView
     * @param RowView    $headerRow
     * @param RowView    $bodyRow
     * @param array      $fieldViews
     */
    public function __construct(
        PagerView $pagerView,
        FormView $formView,
        RouterView $routerView,
        RowView $headerRow,
        RowView $bodyRow,
        array $fieldViews
    ) {
        $this->pagerView = $pagerView;
        $this->filterView = $formView;
        $this->routerView = $routerView;
        $this->bodyRow = $bodyRow;
        $this->headerRow = $headerRow;
        $this->fieldViews = $fieldViews;
        $this->headerRow->init($this, RowView::TYPE_HEADER, null);
    }

    /**
     * @return RowView
     */
    public function getHeaderRow(): RowView
    {
        return $this->headerRow;
    }

    /**
     * @param bool $paged whether to return current page results only
     *                    if false - all results from current page will be returned
     *
     * @return Generator|RowView[]
     */
    public function getBodyRows(bool $paged = true): iterable
    {
        foreach ($this->pagerView->getData() as $value) {
            $this->bodyRow->init($this, RowView::TYPE_BODY, $value);

            yield $this->bodyRow;
        }

        if (!$paged && $this->pagerView->iterateNextPage()) {
            yield from $this->getBodyRows($paged);
        }
    }

    /**
     * @return PagerView
     */
    public function getPager(): PagerView
    {
        return $this->pagerView;
    }

    /**
     * @return RouterView
     */
    public function getRouter(): RouterView
    {
        return $this->routerView;
    }

    /**
     * @return FormView
     */
    public function getFilter(): FormView
    {
        return $this->filterView;
    }

    /**
     * @return FieldView[] field views without value. Use get headerRow | bodyRow to get values.
     */
    public function getFieldViews(): array
    {
        return $this->fieldViews;
    }
}