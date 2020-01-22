# Getting started

## installation

>Package is not yet available via composer.

Register bundle in your `bundles.php` file

```
Povs\ListerBundle\PovsListerBundle::class => ['all' => true]
```

Add configuration to `config/packages/povs_lister.yaml`

``` yaml
    povs_lister:
        # Here you can register list types
        # TypeName: FullyQualifiedClassName
        # There has to be at least one list type and type with name "list" is required
        # Read more about types here: <link> 
        types:
            list: Povs\ListerBundle\Type\ListType\ArrayListType
            export: Povs\ListerBundle\Type\ListType\CsvListType
    
        # Default list config
        # Can be overwritten in list class
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

## Basic usage

### Creating simple list

For this example lets say we have two entities:

```
    User:
        firstName
        lastName
        email
        address (reference to address)

    Address:
        Country        
        streetName
        houseNumber
        
```

And we want to create a list view to return it as json or csv

> To generate html lists for full stack web applications please use https://github.com/povs/ListerTwigBundle

First of all lets create `UserList` class which extends `AbstractList` and provide for 
which entity we are building it via `getDataClass` method. In this case it's User.
``` php
namespace App\Lister;

use Povs\ListerBundle\Definition\AbstractList;

class UsersList extends AbstractList
{
    public function getDataClass(): string
    {
        return User::class;
    }
}
```

Now lets add some fields via buildListFields method. 
This method builds fields for list type with name "list"

``` php
public function buildListFields(ListMapper $listMapper): void
{
    $listMapper->add('firstName', null, ['label' => 'First name'])
        ->add('lastName', null, ['label' => 'Last name'])
        ->add('mail', null, ['label' => 'Mail address'])
        ->add('address.country', null, ['label' => 'Country'])
        ->add('address.streetName', null, ['label' => 'Street name'])
        ->add('address.houseNumber', null, ['label' => 'House number']);
}
```

Lets say we want to remove house number field when exporting.

> Fields configuration naming scheme: build{typeName}Fields.
>
> So for example if we had list type with name xml - we could configure it with buildXmlFields method 

``` php
public function buildExportFields(ListMapper $listMapper): void
{
    $listMapper->build() //Copies fields form buildListFields method
        ->remove('address.houseNumber'); //removes houseNumber field
}
```

To build filters `buildFilterFields` method is used.

``` php
public function buildFilterFields(FilterMapper $filterMapper): void
{
    $filterMapper
        //Filters users by firstName and lastName
        ->add('fullName', null, [
            'query_type' => ComparisonQueryType::class,
            'query_options' => [
                'type' => ComparisonQueryType::COMPARISON_LIKE,
                'wildcard' => ComparisonQueryType::WILDCARD
            ],
            'input_type' => TextType::class,
            'input_options' => ['label' => 'FirstName'],
            'paths' => ['firstName', 'lastName']
        ])
        ->add('address.country', null, [ //Filters users by country
            'query_type' => ContainsQueryType::class,
            'input_type' => ChoiceType::class,
            'input_options' => [
                'multiple' => true,
                'choices' => [] //Countries
            ]
        ]);
}
```

To change configuration for this specific list `configure` method must be used.
Lets say we want to set export file name.

``` php 
public function configure(): array
{
    return [
        'types_configuration' => [
            'export' => [
                'file_name' => 'Users'
            ]
        ]
    ];
}
```

To generate this list in Controller `ListerInterface` must be used. It's aliased as `povs.lister`.
This will generate json response. To generate csv response pass `export` as second parameter

> Array list type can also return results as an array. To get it call `getData` method instead of generateResponse. 

``` php 
use Povs\ListerBundle\Definition\ListerInterface;
use App\Lister\UsersList;

private $lister;

public function __construct(ListerInterface $lister) 
{
    $this->lister = $lister;
}

/**
 * @Route("/users", name="users_list")
 */
public function users()
{
    return $this->lister->buildList(UsersList::class, 'list')
        ->generateResponse();
}
```