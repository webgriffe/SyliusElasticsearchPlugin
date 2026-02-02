# Upgrade plugin guide

## Upgrade from version 06.x to 1.x

In this version, we have updated the plugin to be compatible with version 2 of Sylius.

- The route `@WebgriffeSyliusElasticsearchPlugin/config/shop_routing.php` has been renamed to `@WebgriffeSyliusElasticsearchPlugin/config/routes/shop.php`.
- The frontend has been restructured to follow Sylius 2.x conventions with Twig hooks. Please refer to the Sylius 2.x documentation for guidance on customizing templates using Twig hooks.
