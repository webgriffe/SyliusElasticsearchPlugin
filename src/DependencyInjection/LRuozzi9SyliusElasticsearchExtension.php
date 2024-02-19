<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;

final class LRuozzi9SyliusElasticsearchExtension extends Extension
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
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration();
    }

    private function loadDocumentTypes(ContainerBuilder $container): void
    {
        $documentTypeProviderServiceDefinition = $container->findDefinition('lruozzi9.sylius_elasticsearch_plugin.provider.document_type');
        $taggedServices = $container->findTaggedServiceIds('lruozzi9.sylius_elasticsearch_plugin.document_type');

        foreach ($taggedServices as $id => $tags) {
            $documentTypeProviderServiceDefinition->addMethodCall('addDocumentType', [new Reference($id)]);
        }
    }
}
