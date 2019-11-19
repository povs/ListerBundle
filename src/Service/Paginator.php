<?php
namespace Povs\ListerBundle\Service;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\CountOutputWalker;
use Povs\ListerBundle\Exception\ListQueryException;
use Throwable;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class Paginator
{
    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var string
     */
    private $alias;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var int|null
     */
    private $count;

    /**
     * ListPaginator constructor.
     *
     * @param QueryBuilder       $queryBuilder
     * @param string $alias      query base entity alias
     * @param string $identifier query base entity identifier
     */
    public function __construct(QueryBuilder $queryBuilder, string $alias, string $identifier)
    {
        $this->queryBuilder = $queryBuilder;
        $this->alias = $alias;
        $this->identifier = $identifier;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        if (!$this->count) {
            $queryBuilder = clone $this->queryBuilder;
            $map = new ResultSetMapping();
            $map->addScalarResult('dctrn_count', 'count');

            try {
                $this->count = $queryBuilder->select(sprintf('%s.%s', $this->alias, $this->identifier))
                    ->resetDQLPart('orderBy')
                    ->getQuery()
                    ->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, CountOutputWalker::class)
                    ->setResultSetMapping($map)
                    ->getSingleScalarResult();
            } catch (Throwable $e) {
                throw ListQueryException::invalidQueryConfiguration($e->getMessage(), $queryBuilder->getDQL());
            }
        }

        return $this->count;
    }

    /**
     * @param int $offset
     * @param int $length
     *
     * @return array
     */
    public function getData(int $offset, int $length): array
    {
        $queryBuilder = clone $this->queryBuilder;

        try {
            return $queryBuilder->setFirstResult($offset)
                ->setMaxResults($length)
                ->getQuery()
                ->getResult();
        } catch (Throwable $e) {
            throw ListQueryException::invalidQueryConfiguration($e->getMessage(), $queryBuilder->getDQL());
        }
    }
}