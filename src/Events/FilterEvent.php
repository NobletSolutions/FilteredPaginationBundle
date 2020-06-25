<?php

namespace NS\FilteredPaginationBundle\Events;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\EventDispatcher\Event;

class FilterEvent extends Event
{
    public const
        PRE_FILTER = 'filtered_pagination.pre_filter',
        POST_FILTER = 'filtered_pagination.post_filter';

    /** @var Query|QueryBuilder */
    private $orgQuery;

    /** @var Query|QueryBuilder */
    private $newQuery;

    /**
     * @param Query|QueryBuilder $orgQuery
     */
    public function __construct($orgQuery)
    {
        $this->orgQuery = $orgQuery;
    }

    /**
     * @return Query|QueryBuilder
     */
    public function getOriginalQuery()
    {
        return $this->orgQuery;
    }

    public function hasNewQuery(): bool
    {
        return $this->newQuery !== null;
    }

    /**
     * @return Query|QueryBuilder
     */
    public function getNewQuery()
    {
        return $this->newQuery;
    }

    /**
     * @param Query|QueryBuilder $query
     */
    public function setNewQuery($query): void
    {
        $this->newQuery = $query;
    }
}
