<?php

namespace NS\FilteredPaginationBundle\Tests;

class Configuration
{
    public function getDefaultQueryHints()
    {
        return array();
    }

    public function isSecondLevelCacheEnabled()
    {
        return false;
    }
}
