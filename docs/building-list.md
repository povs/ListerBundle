# Building list

some text

## Setting data class

With `getDataClass` method fullyQualifiedName of entity has to be passed.

```php
public function getDataClass(): string
{
    return User::class;
}
```

## configuring list

To configure list use `configure` method.
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

To pass various parameters when building list use `setParameters` method.

For example:
```php
// List

private $user;

public function setParameters(array $parameters): void
{
    $this->user = $parameters['user']
}
```

Passing parameters to list when building it:

```php
//Some controller

return $this->lister->buildList(MyList::class, 'list', ['user' => $user])
    ->generateResponse();
```


## Building and configuring fields

To build and configure list fields use `build{listType}Fields` method 
where `{listType}` is type name.

For example:
 - To configure list fields for list type with name `list` use `buildListFields` method.
 - With name `export`: `buildExportFields` etc..  
 
> List type with name `list` is mandatory so `buildListFields` method is also mandatory.

```php
public function buildListFields(ListMapper $listMapper): void
{
    $listMapper->add('property', null, ['label' => 'My Label'])
        ->add('field_id', MyFieldType::class, [
            'label' => 'My Label2',
            'path' => 'property.childProperty'
        ])
}

public function buildExportFields(ListMapper $listMapper): void
{
    //Copies all fields which are built in "buildListFields" method
    $listMapper->build();
}
```

### Adding listField

Method `$listMapper->add()` has three parameters:
 - `id` listField identifier. All `.` will be replaced with `_`
 - `fieldType` fullyQualifiedName of fieldType, can be null. More about fieldTypes can be found here
 - `options` array of options
 
> With method `$listMapper->build()` all fields from `buildListField` will be copied.

### ListField options
Option | Value type | Default value | Description
--- | ---| --- | --- 
label | string | null | 
sortable | bool | true | 
sort_value | 'ASC', 'DESC' | null | sorts list by this field
sort_path | string | null | can be used to overwrite sort path (defaults to field path)
path | string, array | null | field path(s) to fetch value (i.e. `['user.firstName', 'user.lastName']`) If value is null, field id will be taken.
join_type | INNER, LEFT | INNER | how field should be joined
property | string, array | null | field properties. Properties will be added to paths. For example `['path' => 'user', 'property' => 'firstName', 'lastName']` will generate paths `['user.firstName', 'user.lastName']`. Will throw an exception if used with multiple paths.
selector | string | 'Povs\ListerBundle\Type\SelectorType\BasicSelectorType' | how value should be fetched from database. More about selector types addLink
view_options | array | [] | options that can be passed to ListView
translate | bool | false | whether to translate field value 
translation_domain | string | null |
translation_prefix | string | null | Translation prefix for example `'user.status.'` with value 1 will translate to `user.status.1`
translate_null | bool | false | whether to translate null value
value | callable | null | To overwrite value with callable function `function($value, $type) {}` where value is value fetched from DB and type is list type.
field_type_options | array | [] | array of options passed to field type
lazy | bool | false | whether to fetch field lazily. On each row it's own query will be executed to fetch all lazy fields. Useful for big tables with paginated results. It's not recommended to use it with results that are not paginated.
position | string | null | Position before which element to insert the field. If null - field will be appended.

## Building and configuring filter fields

To build filter fields use `buildFilterFields` method

```php
public function buildFilterFields(FilterMapper $filterMapper): void
{
    $filterMapper->add('user', null, [
        'query_type' => ComparisonQueryType::class,
        'query_options' => ['type' => ComparisonQueryType::COMPARISON_LIKE, 'wildcard' => ComparisonQueryType::WILDCARD],
        'input_type' => TextType::class,
        'input_options' => ['label' => 'User']
    ]);
}
```

### Adding filterField

Method `$filterMapper->add()` has three parameters:
 - `id` filterField identifier. All `.` will be replaced with `_`
 - `filterType` fullyQualifiedName of filterType, can be null. More about filterTypes can be found here
 - `options` array of options
 
### FilterField options
 Option | Value type | Default value | Description
 --- | ---| --- | --- 
 query_type | string | Povs\ListerBundle\Type\QueryType\ComparisonQueryType | More about queryTypes here 
 query_options | array | [] | Options passed to queryType 
 input_type | string | Symfony\Component\Form\Extension\Core\Type\TextType | Symfony form type 
 input_options | array | [] | Symfony form type options 
 value | mixed | null | initial filter field value 
 mapped | bool | true | If false - field will not filter anything. Used for more complex filtering via `configureQuery` method 
 join_type | INNER, LEFT | INNER | Join type of the field (field will be joined only if filter value is not empty) 
 path | string, array | null | filter join path (same as with listField) 
 property | string, array | null | filter paths (same as with listField) 
 required | bool | false | whether field is required


## Building Join fields (configuring query joins)
 
To add or remove query joins use `buildJoinFields` method.
> This method should be used only in edge cases. If it's not overwritten all required joins will be built automatically.
 
```php
public function buildJoinFields(JoinMapper $joinMapper, ListValueInterface $value): void
{
    //Automatically builds all requried joins for this list
    $joinMapper->build();

    $joinMapper->add('user', 'u');
        ->add('u.address', 'a')
        ->add('a.street', 's');
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
 
 
## Configuring query
 
To change already built query use `configureQuery` method.
There are few use cases for using this method:
   - Filtering data by specific parameters
   - Handling not mapped filterField that is too complex to handle with QueryTypes
   - Debugging query
 
> Avoid using joins in this method. If you need joins use `buildJoinFields()`. 
> It will prevent Query from using duplicate joins (joining same table with different aliases)
 
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

If you're using Symfony autowiring, everything is set.
It will automatically catch all lists that implements `Povs\ListerBundle\Declaration\ListInterface`

Otherwise list has to be registered as services and tagged as `povs_lister.list`

```yaml
# config/services.yaml
services:
    App\Lister\MyList:
        tags: ['povs_lister.list']
```



 