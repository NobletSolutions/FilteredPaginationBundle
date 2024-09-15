<?php

namespace NS\FilteredPaginationBundle\Tests;

class Configuration extends \Doctrine\ORM\Configuration
{
    public function getDefaultQueryHints(): array
    {
        return array();
    }

    public function isSecondLevelCacheEnabled(): bool
    {
        return false;
    }
}
