<?php

namespace NS\FilteredPaginationBundle\Tests;

/**
 * Description of Configuration
 *
 * @author gnat
 */
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