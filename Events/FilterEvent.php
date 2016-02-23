<?php

namespace NS\FilteredPaginationBundle\Events;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\EventDispatcher\Event;

class FilterEvent extends Event
{
    const PRE_FILTER = 'filtered_pagination.pre_filter';

    const POST_FILTER = 'filtered_pagination.post_filter';

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * FilterEvent constructor.
     * @param $queryBuilder
     */
    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }
}
