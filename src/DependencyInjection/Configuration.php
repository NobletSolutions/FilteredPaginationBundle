<?php

namespace NS\FilteredPaginationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        // Instantiating a new TreeBuilder without a constructor arg is deprecated in SF4 and removed in SF5
        if (method_exists(TreeBuilder::class, '__construct')) {
            return new TreeBuilder('ns_filtered_pagination');
        }

        // Included for backward-compatibility with SF3
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('ns_filtered_pagination');
        return $treeBuilder;
    }
}
