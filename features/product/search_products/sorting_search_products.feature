@search_products
Feature: Sorting search products
    In order to see products that fits my needs
    As a Visitor
    I want to be able to search products from a keyword

    Background:
        Given the store operates on a channel named "US Channel" with hostname "127.0.0.1"
        And that channel allows to shop using "English (United States)" and "Italian (Italy)" locales
        And it uses the "English (United States)" locale by default
        And the store operates on another channel named "IT Channel" with hostname "shop.it"
        And that channel allows to shop using "English (United States)" and "Italian (Italy)" locales
        And it uses the "Italian (Italy)" locale by default

        And the store has taxonomy named "T-Shirts" in "English (United States)" locale and "Magliette" in "Italian (Italy)" locale

        And the store has a product "T-Shirt Banana" with code "T_SHIRT_BANANA"
        And this product is available in "US Channel" channel
        And this product is also available in "IT Channel" channel
        And this product is named "Maglietta Banana" in the "Italian (Italy)" locale
        And the description of product "T-Shirt Banana" is "Yellow t-shirt with a banana stamp on it. Composition is 100% cotton. No bananas were harmed during the making of this t-shirt." in the "English (United States)" locale
        And the short description of product "T-Shirt Banana" is "Yellow t-shirt with a banana stamp on it" in the "English (United States)" locale
        And the slug of product "T-Shirt Banana" is "yellow-shirt" in the "English (United States)" locale
        And the meta-description of product "T-Shirt Banana" is "Ut autem corrupti aut quaerat" in the "English (United States)" locale
        And the meta-keywords of product "T-Shirt Banana" are "Neque porro quisquam est" in the "English (United States)" locale
        And this product belongs to "T-Shirts"

        And the store has a product "T-Shirt Apple" available in "US Channel" channel
        And this product is also available in "IT Channel" channel
        And this product is named "Maglietta Mela" in the "Italian (Italy)" locale
        And the description of product "T-Shirt Apple" is "Green t-shirt with an apple stamp on it. Composition is 100% cotton. No apples were harmed during the making of this t-shirt." in the "English (United States)" locale
        And the short description of product "T-Shirt Apple" is "Apple t-shirt lorem ipsum dolor" in the "English (United States)" locale
        And the slug of product "T-Shirt Apple" is "apple-green-shirt-slug" in the "English (United States)" locale
        And this product belongs to "T-Shirts"

        And the store has a "T-Shirt Orange" configurable product
        And this product has a "T-Shirt Orange - Medium size" variant with code "T_SHIRT_ORANGE_M"
        And this product is available in "US Channel" channel
        And this product is also available in "IT Channel" channel
        And this product is named "Maglietta Arancia" in the "Italian (Italy)" locale
        And the description of product "T-Shirt Orange" is "Orange t-shirt with an orange stamp on it. Composition is 100% cotton. No oranges were harmed during the making of this t-shirt." in the "English (United States)" locale
        And the short description of product "T-Shirt Orange" is "Orange t-shirt with an orange stamp on it" in the "English (United States)" locale
        And the slug of product "T-Shirt Orange" is "orange-shirt" in the "English (United States)" locale
        And this product belongs to "T-Shirts"

        And the store is indexed on Elasticsearch
        And I am browsing the channel "US Channel"

    @ui
    Scenario: Sorting products with a not valid direction
        When I try to search for "T_SHIRT_BANANA" with sorting "createdAt" and direction "not valid"
        Then I should see a bad request search page
