<p align="center">
    <a href="https://sylius.com" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" />
    </a>
</p>

<h1 align="center">Sylius <a href="https://www.elastic.co/" target="_blank">Elasticsearch</a> Plugin</h1>

<p align="center">Sylius plugin for Elasticsearch query engine.</p>


## Installation

1. Run:
    ```bash
    composer require webgriffe/sylius-elasticsearch-plugin
   ```

2. Add `Webgriffe\SyliusElasticsearchPlugin\WebgriffeSyliusElasticsearchPlugin::class => ['all' => true]` to your `config/bundles.php`.
   
   Normally, the plugin is automatically added to the `config/bundles.php` file by the `composer require` command. If it is not, you have to add it manually.

3. Import the plugin configs. Add the following to your config/packages/webgriffe_sylius_elasticsearch_plugin.yaml file:
   ```yaml
   imports:
       - { resource: "@WebgriffeSyliusElasticsearchPlugin/config/config.php" }
   ```

4. Import the routes needed for cancelling the payments. Add the following to your config/routes.yaml file:
   ```yaml
   webgriffe_sylius_elasticsearch_plugin:
       resource: "@WebgriffeSyliusElasticsearchPlugin/config/shop_routing.php"
   ```
   **NB:** if you have locales prefix enabled you should prefix import with that.

5. Make your ProductRepository implements the DocumentTypeRepositoryInterface. Remember to update your product resource configuration to use the new repository. For example:
   ```yaml
   sylius_product:
       resources:
           product:
               classes:
                   repository: App\Doctrine\ORM\ProductRepository
   ```

6. Make your ProductAttribute and ProductOption entities implements the FilterableInterface. You can implement it by using our ready DoctrineORMFilterableTrait. For example:
   ```php
use Webgriffe\SyliusElasticsearchPlugin\Doctrine\ORM\FilterableTrait;use Webgriffe\SyliusElasticsearchPlugin\Model\FilterableInterface;
   
   class ProductAttribute implements FilterableInterface
   {
       use FilterableTrait;
   }
   ```

## Usage

## Contributing

For a comprehensive guide on Sylius Plugins development please go to Sylius documentation,
there you will find the <a href="https://docs.sylius.com/en/latest/plugin-development-guide/index.html">Plugin Development Guide</a>, that is full of examples.

### Quickstart Installation

#### Traditional

1. Run `composer create-project sylius/plugin-skeleton ProjectName`.

2. From the plugin skeleton root directory, run the following commands:
   
    ```bash
    $ (cd tests/Application && yarn install)
    $ (cd tests/Application && yarn build)
    $ (cd tests/Application && APP_ENV=test bin/console assets:install public)
    
    $ (cd tests/Application && APP_ENV=test bin/console doctrine:database:create)
    $ (cd tests/Application && APP_ENV=test bin/console doctrine:schema:create)
    ```

To be able to set up a plugin's database, remember to configure you database credentials in `tests/Application/.env` and `tests/Application/.env.test`.

#### Docker

1. Execute `docker compose up -d`

2. Initialize plugin `docker compose exec app make init`

3. See your browser `open localhost`

## Usage

#### Running plugin tests

- PHPUnit
  
  ```bash
  vendor/bin/phpunit
  ```

- PHPSpec
  
  ```bash
  vendor/bin/phpspec run
  ```

- Behat (non-JS scenarios)
  
  ```bash
  vendor/bin/behat --strict --tags="~@javascript"
  ```

- Behat (JS scenarios)
    
    1. [Install Symfony CLI command](https://symfony.com/download).
    
    2. Start Headless Chrome:
  
    ```bash
    google-chrome-stable --enable-automation --disable-background-networking --no-default-browser-check --no-first-run --disable-popup-blocking --disable-default-apps --allow-insecure-localhost --disable-translate --disable-extensions --no-sandbox --enable-features=Metal --headless --remote-debugging-port=9222 --window-size=2880,1800 --proxy-server='direct://' --proxy-bypass-list='*' http://127.0.0.1
    ```
    
    3. Install SSL certificates (only once needed) and run test application's webserver on `127.0.0.1:8080`:
  
    ```bash
    symfony server:ca:install
    APP_ENV=test symfony server:start --port=8080 --dir=tests/Application/public --daemon
    ```
    
    4. Run Behat:
  
    ```bash
    vendor/bin/behat --strict --tags="@javascript"
    ```

- Static Analysis
    
    - Psalm
      
      ```bash
      vendor/bin/psalm
      ```
    
    - PHPStan
      
      ```bash
      vendor/bin/phpstan analyse -c phpstan.neon -l max src/  
      ```

- Coding Standard
  
  ```bash
  vendor/bin/ecs check
  ```

#### Opening Sylius with your plugin

- Using `test` environment:
  
    ```bash
    (cd tests/Application && APP_ENV=test bin/console sylius:fixtures:load)
    (cd tests/Application && APP_ENV=test bin/console server:run -d public)
    ```

- Using `dev` environment:
  
    ```bash
    (cd tests/Application && APP_ENV=dev bin/console sylius:fixtures:load)
    (cd tests/Application && APP_ENV=dev bin/console server:run -d public)
    ```
