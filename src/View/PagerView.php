<?php

namespace Povs\ListerBundle\View;

use Povs\ListerBundle\Service\Paginator;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class PagerView
{
    /**
     * @var int
     */
    private $currentPage;

    /**
     * @var int|null is set when data is fetched
     */
    private $total;

    /**
     * @var int
     */
    private $length;

    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * @var array|null is set when data is fetched
     */
    private $data;

    /**
     * PagerView constructor.
     *
     * @param Paginator $paginator
     * @param int       $currentPage
     * @param int       $perPage
     */
    public function __construct(Paginator $paginator, int $currentPage, int $perPage)
    {
        $this->paginator = $paginator;
        $this->currentPage = $currentPage;
        $this->length = $perPage;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        if (null === $this->total) {
            $this->setData($this->currentPage);
        }

        return $this->total;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @param int|null $page
     *
     * @return int
     */
    public function getFirstResult(?int $page = null): int
    {
        return ($page ?? $this->currentPage) * $this->length - ($this->length - 1);
    }

    /**
     * @param int|null $page
     *
     * @return int
     */
    public function getLastResult(?int $page = null): int
    {
        $lastResult = ($page ?? $this->currentPage) * $this->length;
        $total = $this->getTotal();

        return $lastResult > $total ? $total : $lastResult;
    }

    /**
     * @return int
     */
    public function getLastPage(): int
    {
        return (int) ceil($this->getTotal() / $this->length);
    }

    /**
     * @return int|null
     */
    public function getPrevPage(): ?int
    {
        $prevPage = $this->currentPage - 1;

        if (!$this->validatePage($prevPage)) {
            return null;
        }

        return $prevPage;
    }

    /**
     * @return int|null
     */
    public function getNextPage(): ?int
    {
        $nextPage = $this->currentPage + 1;

        if (!$this->validatePage($nextPage)) {
            return null;
        }

        return $nextPage;
    }

    /**
     * @param int $page
     *
     * @return bool if valid
     */
    public function validatePage(int $page): bool
    {
        return !($page < 1 || ($page !== 1 && ($page * $this->length - ($this->length - 1) > $this->getTotal())));
    }

    /**
     * @param int $page
     *
     * @return bool
     */
    public function iteratePage(int $page): bool
    {
        if (!$this->validatePage($page)) {
            return false;
        }

        $this->setData($page);
        $this->currentPage = $page;

        return true;
    }

    /**
     * Sets ListView to the next page
     *
     * @return bool
     */
    public function iterateNextPage(): bool
    {
        if ($nextPage = $this->getNextPage()) {
            return $this->iteratePage($nextPage);
        }

        return false;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        if (!$this->data) {
            $this->setData($this->currentPage);
        }

        return $this->data;
    }

    /**
     * creates pages array for rendering it in front.
     *
     * @param int $length To determinate pager length. (2*$length+5)
     *                    Basically how much pages has to be added around active page
     *                    for example length 2 with active page 10 and total pages 20 will be rendered like this:
     *                    1 ... 8 9 10 11 12 ... 20
     *                    With length 1:
     *                    1 ... 9 10 11 ... 201
     *
     * @return array = [
     *      'page' => int|null     if null - skip mark should be added (like "...")
     *      'active' => true|false whether this page is equal to current page
     * ][]
     */
    public function getPages(int $length = 1): array
    {
        if ($this->getTotal() === 0) {
            return [];
        }

        $pagerLength = 2 * $length + 5;
        $pages = [];
        $lastPage = $this->getLastPage();

        if ($pagerLength > $lastPage) {
            $pagerLength = $lastPage;
        }

        $range = $this->getRange($pagerLength, $lastPage);

        if ($range[0] !== 1) {
            $range[0] = 1;
            $range[1] = null;
        }

        if ($range[$pagerLength - 1] !== $lastPage) {
            $range[$pagerLength - 1] = $lastPage;
            $range[$pagerLength - 2] = null;
        }

        foreach ($range as $item) {
            $pages[] = [
                'page' => $item,
                'active' => $item === $this->currentPage
            ];
        }

        return $pages;
    }

    /**
     * Sets data for provided page number
     *
     * @param int $page
     */
    private function setData(int $page): void
    {
        $firstResult = $this->getFirstResult($page);
        $this->total = $this->paginator->getCount();
        $this->data = $this->paginator->getData($firstResult - 1, $this->length);
    }

    /**
     * @param int $pagerLength
     * @param int $lastPage
     *
     * @return array
     */
    private function getRange(int $pagerLength, int $lastPage): array
    {
        $lng = (int) floor($pagerLength / 2);
        $from = $this->currentPage - $lng;
        $to = $this->currentPage + $lng;

        if ($from < 1) {
            $diff = 1 - $from;
            $to += $diff;
            $from = 1;
        }

        if ($to > $lastPage) {
            $diff = $to - $lastPage;
            $to = $lastPage;
            $from = $from - $diff < 1 ? 1 : $from - $diff;
        }

        return range($from, $to);
    }
}
