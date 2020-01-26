# Installation

Requirements:
- Php >= 7.1
- Symfony >=4
- Doctrine ORM >=2.6

Install package via `composer`:
>Package is not yet available via composer.

Register bundle in your `bundles.php` file

``` php
Povs\ListerBundle\PovsListerBundle::class => ['all' => true]
```

Add configuration to `config/packages/povs_lister.yaml`

``` yaml
povs_lister:
    # Here you can register list types
    # TypeName: FullyQualifiedClassName
    # There has to be at least one list type and type with name "list" is required
    types:
        list: Povs\ListerBundle\Type\ListType\ArrayListType
        export: Povs\ListerBundle\Type\ListType\CsvListType

    # Default list config
    # Can be modified in list class
    list_config:
        identifier: id #entity identifier
        alias: l #base alias that will be used to build query
        translate: false #whether to translate labels
        translation_domain: null
        multi_column_sort: false #whether to allow sorting by multiple columns
        form_configuration: [] #filter form configuration
        request: #request query names
            page: page 
            length: length
            sort: sort
            filter: null
        type_configuration: #registered types default configuration
            list:
                length: 100 #query per page or query batch
                limit: 0 #length limit (0 - unlimited)
                paged: true #whether results should be paged
            export:
                length: 10000
                file_name: default
                delimiter: ,
                limit: 0 #length limit (0 - unlimited)
```