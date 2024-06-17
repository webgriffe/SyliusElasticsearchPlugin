<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('webgriffe_sylius_elasticsearch_plugin');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('search_query_template')
                    ->defaultValue('@WebgriffeSyliusElasticsearchPlugin/query/search/query.json.twig')
                    ->info('The path of the template file used to render the search query. It must be a Twig template.')
                ->end()
                ->scalarNode('taxon_query_template')
                    ->defaultValue('@WebgriffeSyliusElasticsearchPlugin/query/taxon/query.json.twig')
                    ->info('The path of the template file used to render the taxon query. It must be a Twig template.')
                ->end()
                ->arrayNode('server')
                    ->ignoreExtraKeys()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('host')
                            ->defaultValue('127.0.0.1')
                            ->info('The host of the Elasticsearch server.')
                        ->end()
                        ->scalarNode('port')
                            ->defaultValue('9200')
                            ->info('The port of the Elasticsearch server.')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('search')
                    ->ignoreExtraKeys()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('page_limit')
                            ->defaultValue(9)
                            ->info('Number of products per page in the search results.')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('taxon')
                    ->ignoreExtraKeys()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('page_limit')
                            ->defaultValue(9)
                            ->info('Number of products per page in the taxon results.')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('instant_search')
                    ->ignoreExtraKeys()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('page_limit')
                            ->defaultValue(8)
                            ->info('Number of products per page in the instant search results.')
                        ->end()
                        ->scalarNode('completion_suggesters_size')
                            ->defaultValue(5)
                            ->info('Number of completion suggestions to show in the instant search results.')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
