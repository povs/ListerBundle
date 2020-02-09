# Selector types

Selector type is a layer between database and your fields.
It is responsible for adding select statements and parsing query results.

Selector type is set when adding list field via `selector` option

## Available selector types

### Basic selector type
Used for majority of use cases. It is used as a default selector type for all list fields.
It will return direct value by path(s). If multiple paths are passed it will return an array of those results.

> All selects will be aliased as `{field_id}_field_{index}` where index is path index.

### Count selector type
Used to count results joined with one-to-many or many-to-many relations.

For example lets say we want to add field which represents how much items does order have.

````php
// Some list

public function buildListFields(ListMapper $listMapper): void
{
    $listMapper->add('order.items.id', null, [
        'label' => 'Order items count',
        'selector' => CountSelectorType::class,
        'lazy' => true
    ])
}
````

### Group selector type
Will select results with sql `GROUP_CONCAT` function.
Used when selecting from one-to-many or many-to-many joined entities.

>DQL do not support GROUP_CONCAT clause. So to use this selector you will need your own group concat extension or use [doctrine extensions](https://github.com/beberlei/DoctrineExtensions).
>When installed add this to your doctrine configuration:
 
 ````yaml
orm:
    dql:
        string_functions:
            GROUP_CONCAT: DoctrineExtensions\Query\Mysql\GroupConcat
 ````

Results will be returned as one dimensional array. If multiple paths are passed - two dimensional.

## Creating your own selector type

Selector types has to implement `SelectorTypeInterface` but it's best to just extend `BasicSelectorType`

SelectorTypeInterface implements four methods:
 - `apply()` Used to apply select statements to query builder
 - `getValue()` Used to parse data for a field
 - `getSortPath()` By what path this field should be sorted
 - `hasAggregation()` Whether to apply group by clause to the main query when using this selector.