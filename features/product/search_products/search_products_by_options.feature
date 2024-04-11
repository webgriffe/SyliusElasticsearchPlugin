@search_products
Feature: Search products by options
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

        And the store has a product "T-Shirt Apple" available in "US Channel" channel
        And this product is also available in "IT Channel" channel

        And the store has a "T-Shirt Orange" configurable product
        And this product has a "T-Shirt Orange - Medium size" variant with code "T_SHIRT_ORANGE_M"
        And this product is available in "US Channel" channel
        And this product is also available in "IT Channel" channel

        And the store is indexed on Elasticsearch
        And I am browsing the channel "US Channel"
