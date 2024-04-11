@search_products
Feature: Search products
    In order to find products that fits my needs
    As a Visitor
    I want to be able to search products from a keyword

    Background:
        Given the store operates on a channel named "US Channel" with hostname "127.0.0.1"
        And that channel allows to shop using "English (United States)" and "Italian (Italy)" locales
        And it uses the "English (United States)" locale by default
        And the store operates on another channel named "IT Channel" with hostname "shop.it"
        And that channel allows to shop using "English (United States)" and "Italian (Italy)" locales
        And it uses the "Italian (Italy)" locale by default

        And the store has a product "T-Shirt Banana" with code "T_SHIRT_BANANA"
        And this product is available in "US Channel" channel
        And this product is also available in "IT Channel" channel
        And this product is named "Maglietta Banana" in the "Italian (Italy)" locale

        And the store has a product "T-Shirt Apple" available in "US Channel" channel
        And this product is also available in "IT Channel" channel
        And this product is named "Maglietta Mela" in the "Italian (Italy)" locale

        And the store has a "T-Shirt Orange" configurable product
        And this product has a "T-Shirt Orange - Medium size" variant with code "T_SHIRT_ORANGE_M"
        And this product is available in "US Channel" channel
        And this product is also available in "IT Channel" channel
        And this product is named "Maglietta Arancia" in the "Italian (Italy)" locale

        And the store is indexed on Elasticsearch
        And I am browsing the channel "US Channel"

    @ui
    Scenario: Search products by code
        When I search for "T_SHIRT_BANANA"
        Then I should be redirected to the product "T-Shirt Banana" page

    @ui
    Scenario: Search products by variant code
        When I search for "T_SHIRT_ORANGE_M"
        Then I should be redirected to the product "T-Shirt Orange" page

    @ui
    Scenario: Search products by name
        When I search for "shirt"
        Then I should see "3" results
        And I should see the product "T-Shirt Banana"
        And I should see the product "T-Shirt Apple"
        And I should see the product "T-Shirt Orange"

    @ui
    Scenario: Search products by name in different locale
        Given I am browsing the channel "IT Channel"
        When I search for "maglietta"
        Then I should see "3" results
        And I should see the product "Maglietta Banana"
        And I should see the product "Maglietta Mela"
        And I should see the product "Maglietta Arancia"

    @ui
    Scenario: Search products by variant name
        When I search for "medium"
        Then I should be redirected to the product "T-Shirt Orange" page

    @ui
    Scenario: Search only enabled products
        Given the store has a product "T-Shirt disabled" available in "US Channel" channel
        And this product has been disabled
        And the store is indexed on Elasticsearch
        When I search for "shirt"
        Then I should see "3" results
