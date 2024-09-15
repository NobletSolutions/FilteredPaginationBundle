<?php

namespace NS\FilteredPaginationBundle\Events;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\EventDispatcher\Event;

class FilterEvent extends Event
{
    public const string
        PRE_FILTER = 'filtered_pagination.pre_filter',
        POST_FILTER = 'filtered_pagination.post_filter';

    private Query|QueryBuilder $orgQuery;

    private null|Query|QueryBuilder $newQuery = null;

    public function __construct(Query|QueryBuilder $orgQuery)
    {
        $this->orgQuery = $orgQuery;
    }

    public function getOriginalQuery(): Query|QueryBuilder
    {
        return $this->orgQuery;
    }

    public function hasNewQuery(): bool
    {
        return $this->newQuery !== null;
    }

    public function getNewQuery(): null|Query|QueryBuilder
    {
        return $this->newQuery;
    }

    public function setNewQuery(Query|QueryBuilder $query): void
    {
        $this->newQuery = $query;
    }
}
