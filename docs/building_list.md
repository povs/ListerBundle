# Building list

## Creating list class

All lists have to implement `Povs\ListerBundle\Declaration\ListInterface` or extend `Povs\ListerBundle\Declaration\AbstractList`,
which makes creating lists even easier.

```php
use Povs\ListerBundle\Declaration\AbstractList;

class MyList extends AbstractList
{
    //...
}
```

## Setting data class

With `getDataClass` method fully qualified name of entity has to be returned.

```php
public function getDataClass(): string
{
    return User::class;
}
```

## Configuring list

To configure list use `configure()` method.
Array returned via this method will be merged with configuration under `list_configuration`

```php
public function configure(): array
{
    return [
        'translate' => true,
        'identifier' => 'entity_id',
        'type_configuration' => [
            'list' => [
                'limit' => 5000
            ]
        ]
    ]
}
```

## Passing custom parameters to the list

To pass various parameters when building list use `setParameters()` method.

```php
// List

private $user;

public function setParameters(array $parameters): void
{
    $this->user = $parameters['user'];
}
```

Passing parameters to the list when building it:

```php

//Povs\ListerBundle\Declaration\ListerInterface
private $lister;

$this->lister->buildList(MyList::class, 'list', ['user' => $user])
    ->generateResponse();
```

## Building and configuring fields

To build and configure list fields use `build{listType}Fields` method 
where `{listType}` is type name.
 - To configure list fields for list type with name `list` use `buildListFields()` method.
 - With name `export` - `buildExportFields()` etc..  
 
> List type with name `list` is mandatory so `buildListFields()` method is also mandatory.

> If method for other list types is not overwritten, all fields from `buildListFields()` will automatically be copied.

```php
public function buildListFields(ListMapper $listMapper): void
{
    $listMapper->add('field_id_1', null, ['label' => 'My Label'])
        ->add('field_id_2', MyFieldType::class, [
            'label' => 'My Label2',
            'path' => 'property.childProperty'
        ]);
}

public function buildExportFields(ListMapper $listMapper): void
{
    $listMapper->build() //Copies all fields which are built in "buildListFields" method
        ->get('field_id_1')->setOption('label', 'Export label') //Overwrites field_id_1 label
        ->add('field_id_3', null, [  //Adds field_id_3 to the export type
            'label' => 'My label3'
        ]);
}
```

### Adding listField

Method `$listMapper->add()` has three parameters:
 - `id` field identifier. All `.` will be replaced with `_`
 - `fieldType` fully qualified name of [field type](types/field.md), can be null.
 - `options` array of options
 
> With method `$listMapper->build()` all fields from `buildListField()` will be copied.

### ListField options
Option | Value type | Default value | Description
--- | ---| --- | --- 
label | string | null | 
sortable | bool | true | 
sort_value | 'ASC', 'DESC' | null | sorts list by this field
sort_path | string | null | can be used to overwrite sort path (defaults to field path)
path | string, array | null | field path(s) to fetch value (i.e. `['user.firstName', 'user.lastName']`) If value is null, field id will be taken.
join_type | INNER, LEFT | INNER | how field should be joined in the query
property | string, array | null | properties will be added to paths. For example `['path' => 'user', 'property' => 'firstName', 'lastName']` will generate paths `['user.firstName', 'user.lastName']`. Will throw an exception if used with multiple paths.
selector | string | 'BasicSelectorType' | how value should be fetched from the database. More about selector types [here](types/selector.md)
view_options | array | [] | options that can be passed to [list view field](views.md) 
translate | bool | false | whether to translate field value 
translation_domain | string | null |
translation_prefix | string | null | Adds translation prefix to field value. e.g. `'user.status.'` with value 1 will translate to `user.status.1`
translate_null | bool | false | whether to translate null value
value | callable | null | To overwrite value with callable function `function($value, $type) {}`.
field_type_options | array | [] | array of options passed to [field type](types/field.md)
lazy | bool | false | whether to fetch field lazily. On each row it's own query will be executed to fetch all lazy fields. Useful for big tables with paginated results. More about it [here](optimisation.md)
position | string | null | Position before which element to insert the field. If null - field will be appended. Useful when extending from other lists.

## Building and configuring filter fields

To build filter fields use `buildFilterFields()` method

```php
public function buildFilterFields(FilterMapper $filterMapper): void
{
    $filterMapper->add('user', null, [
        'query_type' => ComparisonQueryType::class,
        'query_options' => [
            'type' => ComparisonQueryType::COMPARISON_LIKE, 
            'wildcard' => ComparisonQueryType::WILDCARD
        ],
        'input_type' => TextType::class,
        'input_options' => ['label' => 'User']
    ]);
}
```

### Adding filterField

Method `$filterMapper->add()` has three parameters:
 - `id` field identifier. All `.` will be replaced with `_`
 - `filterType` fully qualified name of [filter type](types/filter.md), can be null.
 - `options` array of options
 
### FilterField options
 Option | Value type | Default value | Description
 --- | ---| --- | --- 
 query_type | string | ComparisonQueryType | Which [query type](types/query.md) to use
 query_options | array | [] | Options passed to query type 
 input_type | string | TextType | Symfony [form type](https://symfony.com/doc/current/reference/forms/types.html)
 input_options | array | [] | Symfony form type options 
 value | mixed | null | initial filter field value 
 mapped | bool | true | If false - field will not filter anything. Used for more complex filtering via `configureQuery` method 
 join_type | INNER, LEFT | INNER | Join type of the field (field will be joined only if filter value is not empty) 
 path | string, array | null | filter join path (same as with list field) 
 property | string, array | null | filter paths (same as with list field) 
 required | bool | false | whether field is required


## Building Join fields (configuring query joins)
 
To add or remove query joins use `buildJoinFields()` method.
> This method should be used only in edge cases. If it's not overwritten all required joins will be built automatically.
 
```php
public function buildJoinFields(JoinMapper $joinMapper, ListValueInterface $value): void
{
    //Automatically builds all requried joins for this list
    $joinMapper->build();

    //Overwrites u.address join alias
    $joinMapper->getByPath('u.address', false)->setAlias('a')
    //Adds street left join to u.address with name condition;
        ->add('a.street', 's', [
            'condition' => 's.name = :street_name', 
            'condition_parameters' => ['street_name' => 'street_name_param_value'],
            'condition_type' => 'WITH',
            'join_type' => 'LEFT'
        ]);
}
```

### Adding joinField 

Method `$joinMapper->add()` has three parameters:
 - `path` join path
 - `alias` join alias
 - `options` array of options
 
> With method `$joinMapper->build()` all required joins will be added automatically

### JoinField options
Option | Value type | Default value | Description
--- | ---| --- | --- 
join_type | INNER, LEFT | INNER | 
lazy | bool | false | whether to add this join in the query only when fetching for lazy data.
condition | string | null | condition on which to join the field
condition_type | string | WITH | condition type (WITH, ON)
condition_parameters | array | null | array of parameters for condition
 
 
## Configuring query
 
To change already built query use `configureQuery` method.
There are few use cases for using this method:
   - Filtering data by specific parameters
   - Handling not mapped filter field that is too complex to handle with [query types](types/query.md)
   - Debugging the query
 
> Avoid using joins in this method. If you need joins use `buildJoinFields()`. 
> It will prevent query from using duplicate joins (joining same table with different aliases)
 
```php
public function configureQuery(QueryBuilder $queryBuilder, ListValueInterface $value): void
{
    $queryBuilder->andWhere('l.user = :user')
        ->setParameter('user', $this->user)

    if ($value = $value->getFilterValue('very_complex_filter')) {
        //...
    }
}
```

## Registering your list

If you're using the [default services.yaml configuration](https://symfony.com/doc/current/service_container.html#service-container-services-load-example), you're done! 
It will automatically catch all lists that implements `Povs\ListerBundle\Declaration\ListInterface` and tags it.

Otherwise list has to be registered as service and tagged as `povs_lister.list`

```yaml
# config/services.yaml
services:
    App\Lister\MyList:
        tags: ['povs_lister.list']
```



 