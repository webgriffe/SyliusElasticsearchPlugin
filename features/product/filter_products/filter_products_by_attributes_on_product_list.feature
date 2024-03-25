@filter_products
Feature: Filter products by attributes from a specific taxon
    In order to find products that fits my needs
    As a Visitor
    I want to be able to filter products from a specific taxon

    Background:
        Given the store operates on a channel named "Default"
        And the store has "T-Shirts" taxonomy

        And the store has a text product attribute "T-Shirt material"
        And the product attribute "T-Shirt material" is filterable
        And the store has a select product attribute "Brand"
        And this product attribute has a value "Nike" in "English (United States)" locale
        And this product attribute has a value "Adidas" in "English (United States)" locale
        And the product attribute "Brand" is filterable

        And the store has a product "T-Shirt Banana" available in "Default" channel
        And this product belongs to "T-Shirts"
        And this product has a text attribute "T-Shirt material" with value "Cotton"
        And this product has also a select attribute "Brand" with value "Nike"

        And the store has a product "T-Shirt Apple" available in "Default" channel
        And this product belongs to "T-Shirts"
        And this product has a text attribute "T-Shirt material" with value "Linen"
        And this product has also a select attribute "Brand" with value "Nike"

        And the store has a product "T-Shirt Orange" available in "Default" channel
        And this product belongs to "T-Shirts"
        And this product has a text attribute "T-Shirt material" with value "Cotton"
        And this product has also a select attribute "Brand" with value "Adidas"

        And the store is indexed on Elasticsearch

    @ui
    Scenario: Filter products by text attribute from a specific taxon
        When I browse products from taxon "T-Shirts"
        Then I should see the filter "T-Shirt material"
        And I should see the value "Cotton" for filter "T-Shirt material" with counter "2"
        And I should see the value "Linen" for filter "T-Shirt material" with counter "1"
        When I filter products by "T-Shirt material" with value "Linen"
        Then I should see the product "T-Shirt Apple"
        And I should not see the product "T-Shirt Banana"
        And I should not see the product "T-Shirt Orange"

    @ui
    Scenario: Filter products by select attribute from a specific taxon
        When I browse products from taxon "T-Shirts"
        Then I should see the filter "Brand"
        And I should see the value "Nike" for filter "Brand" with counter "2"
        And I should see the value "Adidas" for filter "Brand" with counter "1"
        When I filter products by "Brand" with value "Adidas"
        Then I should see the product "T-Shirt Orange"
        And I should not see the product "T-Shirt Banana"
        And I should not see the product "T-Shirt Apple"
