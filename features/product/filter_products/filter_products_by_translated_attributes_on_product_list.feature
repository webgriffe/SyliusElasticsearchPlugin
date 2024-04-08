@filter_products
Feature: Filter products by translated attributes from a specific taxon
    In order to find products that fits my needs
    As a Visitor
    I want to be able to filter products from a specific taxon

    Background:
        Given the store operates on a channel named "US Channel" with hostname "127.0.0.1"
        And that channel allows to shop using "English (United States)" and "Italian (Italy)" locales
        And it uses the "English (United States)" locale by default
        And the store operates on another channel named "IT Channel" with hostname "shop.it"
        And that channel allows to shop using "English (United States)" and "Italian (Italy)" locales
        And it uses the "Italian (Italy)" locale by default

        And the store has taxonomy named "T-Shirts" in "English (United States)" locale and "Magliette" in "Italian (Italy)" locale

        And the store has a text product attribute "T-Shirt material"
        And this product attribute has translation "Materiale maglietta" in "Italian (Italy)" locale
        And the store has a integer product attribute "Year"
        And this product attribute has translation "Anno" in "Italian (Italy)" locale
        And the store has a percent product attribute "Recycled"
        And this product attribute has translation "Riciclato" in "Italian (Italy)" locale
        And the store has a select product attribute "Brand"
        And this product attribute has translation "Marca" in "Italian (Italy)" locale
        And this product attribute has a value "Nike" in "English (United States)" locale
        And this product attribute has a value "Adidas" in "English (United States)" locale
        And the store has a textarea product attribute "Description"
        And this product attribute has translation "Descrizione" in "Italian (Italy)" locale
        And the store has a checkbox product attribute "T-Shirt eco-friendly"
        And this product attribute has translation "Maglietta amica dell'ambiente" in "Italian (Italy)" locale
        And the store has a date product attribute "Date of production"
        And this product attribute has translation "Data di produzione" in "Italian (Italy)" locale
        And the store has a datetime product attribute "Valid to"
        And this product attribute has translation "Valido fino" in "Italian (Italy)" locale

        And the store has a product "T-Shirt Banana" available in "US Channel" channel
        And this product is also available in "IT Channel" channel
        And this product belongs to "T-Shirts"
        And this product has a text attribute "T-Shirt material" with value "Cotton" in "English (United States)" locale
        And this product has a text attribute "T-Shirt material" with value "Cotone" in "Italian (Italy)" locale
        And this product has a select attribute "Brand" with value "Nike"
        And this product has a textarea attribute "Description" with value "Yellow t-shirt with a banana print" in "English (United States)" locale
        And this product has a textarea attribute "Description" with value "Maglietta gialla con stampa di una banana" in "Italian (Italy)" locale
        And this product has a "checkbox" attribute "T-Shirt eco-friendly" set to "Yes"
        And this product has an integer attribute "Year" with value "2024"
        And this product has a percent attribute "Recycled" with value "83.5"
        And this product has a date attribute "Date of production" with date "01 April 2023"
        And this product has a datetime attribute "Valid to" with date "30 November 2025 12:30"

        And the store has a product "T-Shirt Apple" available in "US Channel" channel
        And this product is also available in "IT Channel" channel
        And this product belongs to "T-Shirts"
        And this product has a text attribute "T-Shirt material" with value "Linen" in "English (United States)" locale
        And this product has a text attribute "T-Shirt material" with value "Lino" in "Italian (Italy)" locale
        And this product has a select attribute "Brand" with value "Nike"
        And this product has a textarea attribute "Description" with value "Green t-shirt with an apple print" in "English (United States)" locale
        And this product has a textarea attribute "Description" with value "Maglietta verde con stampa di una mela" in "Italian (Italy)" locale
        And this product has a "checkbox" attribute "T-Shirt eco-friendly" set to "Yes"
        And this product has an integer attribute "Year" with value "2023"
        And this product has a percent attribute "Recycled" with value "95.6"
        And this product has a date attribute "Date of production" with date "01 May 2023"
        And this product has a datetime attribute "Valid to" with date "30 November 2025 12:30"

        And the store has a product "T-Shirt Orange" available in "US Channel" channel
        And this product is also available in "IT Channel" channel
        And this product belongs to "T-Shirts"
        And this product has a text attribute "T-Shirt material" with value "Cotton" in "English (United States)" locale
        And this product has a text attribute "T-Shirt material" with value "Cotone" in "Italian (Italy)" locale
        And this product has a select attribute "Brand" with value "Adidas"
        And this product has a textarea attribute "Description" with value "Orange t-shirt with an orange print" in "English (United States)" locale
        And this product has a textarea attribute "Description" with value "Maglietta arancione con stampa di una arancia" in "Italian (Italy)" locale
        And this product has a "checkbox" attribute "T-Shirt eco-friendly" set to "No"
        And this product has a percent attribute "Recycled" with value "0"
        And this product has a date attribute "Date of production" with date "30 June 2023"
        And this product has a datetime attribute "Valid to" with date "29 November 2025 18:45"

        And the product attribute "T-Shirt material" is filterable
        And the product attribute "Brand" is filterable
        And the product attribute "Description" is filterable
        And the product attribute "T-Shirt eco-friendly" is filterable
        And the product attribute "Year" is filterable
        And the product attribute "Recycled" is filterable
        And the product attribute "Date of production" is filterable
        And the product attribute "Valid to" is filterable

        And the store is indexed on Elasticsearch
        Given I am browsing the channel "US Channel"

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

    @ui
    Scenario: Filter products by textarea attribute from a specific taxon
        When I browse products from taxon "T-Shirts"
        Then I should see the filter "T-Shirt material"
        And I should see the value "Cotton" for filter "T-Shirt material" with counter "2"
        And I should see the value "Linen" for filter "T-Shirt material" with counter "1"
        When I filter products by "T-Shirt material" with value "Linen"
        Then I should see the product "T-Shirt Apple"
        And I should not see the product "T-Shirt Banana"
        And I should not see the product "T-Shirt Orange"

    @ui
    Scenario: Filter products by boolean attribute from a specific taxon
        When I browse products from taxon "T-Shirts"
        Then I should see the filter "T-Shirt eco-friendly"
        And I should see the value "true" for filter "T-Shirt eco-friendly" with counter "2"
        And I should see the value "false" for filter "T-Shirt eco-friendly" with counter "1"
        When I filter products by "T-Shirt eco-friendly" with value "true"
        Then I should see the product "T-Shirt Banana"
        And I should see the product "T-Shirt Apple"
        And I should not see the product "T-Shirt Orange"

    @ui
    Scenario: Filter products by integer attribute from a specific taxon
        When I browse products from taxon "T-Shirts"
        Then I should see the filter "Year"
        And I should see the value "2024" for filter "Year" with counter "1"
        And I should see the value "2023" for filter "Year" with counter "1"
        When I filter products by "Year" with value "2023"
        Then I should see the product "T-Shirt Apple"
        And I should not see the product "T-Shirt Banana"
        And I should not see the product "T-Shirt Orange"

    @ui
    Scenario: Filter products by decimal attribute from a specific taxon
        When I browse products from taxon "T-Shirts"
        Then I should see the filter "Recycled"
        And I should see the value "0.0" for filter "Recycled" with counter "1"
        And I should see the value "83.5" for filter "Recycled" with counter "1"
        And I should see the value "95.6" for filter "Recycled" with counter "1"
        When I filter products by "Recycled" with value "83.5"
        Then I should see the product "T-Shirt Banana"
        And I should not see the product "T-Shirt Apple"
        And I should not see the product "T-Shirt Orange"

    @ui
    Scenario: Filter products by date attribute from a specific taxon
        When I browse products from taxon "T-Shirts"
        Then I should see the filter "Date of production"
        And I should see the value "2023-04-01" for filter "Date of production" with counter "1"
        And I should see the value "2023-05-01" for filter "Date of production" with counter "1"
        And I should see the value "2023-06-30" for filter "Date of production" with counter "1"
        When I filter products by "Date of production" with value "2023-05-01"
        Then I should see the product "T-Shirt Apple"
        And I should not see the product "T-Shirt Banana"
        And I should not see the product "T-Shirt Orange"

    @ui
    Scenario: Filter products by date time attribute from a specific taxon
        When I browse products from taxon "T-Shirts"
        Then I should see the filter "Valid to"
        And I should see the value "2025-11-30 12:30:00" for filter "Valid to" with counter "2"
        And I should see the value "2025-11-29 18:45:00" for filter "Valid to" with counter "1"
        When I filter products by "Valid to" with value "2025-11-30 12:30:00"
        Then I should see the product "T-Shirt Apple"
        And I should see the product "T-Shirt Banana"
        And I should not see the product "T-Shirt Orange"
