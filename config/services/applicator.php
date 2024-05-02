<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\Bundle\CoreBundle\CatalogPromotion\Applicator\CatalogPromotionApplicatorInterface;
use Webgriffe\SyliusElasticsearchPlugin\Applicator\CatalogPromotionApplicator;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe.sylius_elasticsearch_plugin.applicator.catalog_promotion', CatalogPromotionApplicator::class)
        ->decorate( CatalogPromotionApplicatorInterface::class)
        ->args([
            service('.inner'),
            service('webgriffe.sylius_elasticsearch_plugin.index_manager.elasticsearch'),
            service('webgriffe.sylius_elasticsearch_plugin.provider.document_type'),
        ])
    ;
};
