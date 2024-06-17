<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;

final class WebgriffeSyliusElasticsearchExtension extends Extension
{
    /**
     * @psalm-suppress UnusedVariable
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $loader->load('services.php');

        $this->loadDocumentTypes($container);

        $this->addTemplateFilePathsToTwigQueryBuilder($container, $config);
        $this->addServerArgumentsToClient($container, $config);
        $this->addDefaultSearchQueryValues($container, $config);
        $this->addDefaultTaxonQueryValues($container, $config);
        $this->addDefaultInstantSearchQueryValues($container, $config);
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration();
    }

    private function loadDocumentTypes(ContainerBuilder $container): void
    {
        $documentTypeProviderServiceDefinition = $container->findDefinition('webgriffe.sylius_elasticsearch_plugin.provider.document_type');
        $taggedServices = $container->findTaggedServiceIds('webgriffe.sylius_elasticsearch_plugin.document_type');

        foreach (array_keys($taggedServices) as $serviceId) {
            $documentTypeProviderServiceDefinition->addMethodCall('addDocumentType', [new Reference($serviceId)]);
        }
    }

    private function addTemplateFilePathsToTwigQueryBuilder(ContainerBuilder $container, array $config): void
    {
        $definition = $container->getDefinition('webgriffe.sylius_elasticsearch_plugin.builder.query');
        $definition->setArgument('$searchQueryTemplate', $config['search_query_template']);
        $definition->setArgument('$taxonQueryTemplate', $config['taxon_query_template']);
    }

    private function addServerArgumentsToClient(ContainerBuilder $container, array $config): void
    {
        $definition = $container->getDefinition('webgriffe.sylius_elasticsearch_plugin.client');
        $definition->setArgument('$host', $config['server']['host']);
        $definition->setArgument('$port', $config['server']['port']);
    }

    private function addDefaultSearchQueryValues(ContainerBuilder $container, array $config): void
    {
        $definition = $container->getDefinition('webgriffe.sylius_elasticsearch_plugin.controller.search');
        $definition->setArgument('$searchDefaultPageLimit', $config['search']['page_limit']);
    }

    private function addDefaultTaxonQueryValues(ContainerBuilder $container, array $config): void
    {
        $definition = $container->getDefinition('webgriffe.sylius_elasticsearch_plugin.controller.product');
        $definition->setArgument('$taxonDefaultPageLimit', $config['taxon']['page_limit']);
    }

    private function addDefaultInstantSearchQueryValues(ContainerBuilder $container, array $config): void
    {
        $definition = $container->getDefinition('webgriffe.sylius_elasticsearch_plugin.controller.instant_search');
        $definition->setArgument('$maxResults', $config['instant_search']['page_limit']);
        $definition->setArgument('$completionSuggestersSize', $config['instant_search']['completion_suggesters_size']);
    }
}
