<?php

namespace NS\FilteredPaginationBundle\Events;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\EventDispatcher\Event;

class FilterEvent extends Event
{
    const PRE_FILTER = 'filtered_pagination.pre_filter';

    const POST_FILTER = 'filtered_pagination.post_filter';

    /**
     * @var Query|QueryBuilder
     */
    private $orgQuery;

    /**
     * @var Query|QueryBuilder
     */
    private $newQuery;

    /**
     * FilterEvent constructor.
     * @param $orgQuery
     */
    public function __construct($orgQuery)
    {
        $this->orgQuery = $orgQuery;
    }

    /**
     * @return QueryBuilder
     */
    public function getOriginalQuery()
    {
        return $this->orgQuery;
    }

    /**
     * @return bool
     */
    public function hasNewQuery()
    {
        return ($this->newQuery !== null);
    }

    /**
     * @return Query
     */
    public function getNewQuery()
    {
        return $this->newQuery;
    }

    /**
     * @param Query $query
     * @return FilterEvent
     */
    public function setNewQuery($query)
    {
        $this->newQuery = $query;
        return $this;
    }
}
