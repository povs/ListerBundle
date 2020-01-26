# Query types

## Available query types

### Comparison query type

Comparison query type used to directly compare filter value with database value.

>Multiple paths passed to this query type will merge it as a single string with MySql `CONCAT_WS` function.
>So for example `path => ['foo', 'bar']` will generate query `WHERE CONCAT_WS(' ',foo,bar) = :value`, where space is `delimiter` option.
>
>CONCAT_WS is not supported by doctrine. So to use this type with multiple paths you will need your own concat_ws extension or use [doctrine extensions](https://github.com/beberlei/DoctrineExtensions)
 
This query accepts three options passed via `query_options`

Option | Type | Default value 
--- | --- | ---
type | string (=, >, >=, <, <=, <>, LIKE) | = 
wildcard | string (no_wildcard, wildcard_start, wildcard_end, wildcard) | no_wildcard 
delimiter | string | single space

### Contains query type

Contains query type used to generate `IN (:values)` query.
Value coming from filter has to be an array.
This query type has no query options.

> If multiple paths are passed to this query only first one will be used.

### Between Query type 

Used to generate `BETWEEN (:val1 AND :val2)` query.
If value coming from filter is an array: `val1` will be taken by `0` index and `val2` by `1`.
If value is a string, it will be converted to an array (by exploding by `value_delimiter` query option)

Option | Type | Default value 
--- | --- | ---
value_delimiter | string | -

### Having query type

Will generate having query by `type` and `function` options.
For example if type is `>=` and function is `count`, generated query will look like this: `HAVING (COUNT(path) >= :value)`

This query accepts two options passed via `query_options`

Option | Type | Default value 
--- | --- | ---
type | string (=, >, >=, <, <=, <>) | = 
function | string (count, sum, avg, min, max) | count

> If multiple paths are passed to this query only first one will be used.

### Match query type

DQL do not support match against clause. So to use this type you will need your own group concat extension or use [doctrine extensions](https://github.com/beberlei/DoctrineExtensions).
When installed add this to your doctrine configuration:

```` yaml
orm:
    dql:
        string_functions:
            MATCH: DoctrineExtensions\Query\Mysql\MatchAgainst
````

Options:

Option | Type | Default value 
--- | --- | --- 
relevance | int, double | 0  
boolean | bool | false 
expand | bool | false

Example:

Lets say options are as follows: 1, true, false and paths are `[foo, bar]`
This will generate query like this: `MATCH (foo,bar) AGAINST (':filter_value' IN BOOLEAN MODE) > 1`


## Creating your own query type

Custom query types has to extend `Povs\ListerBundle\Type\QueryType\AbstractQueryType` 

### Configuring query 
The most important method is `filter` which arguments are:
 - $queryBuilder - doctrine query builder
 - array $paths - array of paths 
 - string $identifier - field unique id (for binding query parameters)
 - $value - value from filter
 
In this method you can add filter query however you want.

> It is important to use `andWhere`, `andHaving` instead of `where`, `having` methods so it does not overwrites previous ones.
 
### Configuring options

If query type depends on options you can overwrite `configureOptions` method.
All options to query type are passed via `query_options` when building filter field.

> To get options use `$this->getOption('name')` inside your query type.

### Setting aggregation

If query requires aggregation (i.e. `Having`), overwrite `hasAggregation` method with return value of true.

In that case query will be grouped by data class identifier if this query type will be used (e.g. data will be filtered by filter field using this query type).

### Basic example of query type

this query type is used only as an example. `ComparisonQueryType` is built in to achieve exactly that and more.

````php 
namespace App\Lister\QueryType;

use Doctrine\ORM\QueryBuilder;
use Povs\ListerBundle\Type\QueryType\AbstractQueryType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BasicQueryType extends AbstractQueryType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(['comparison'])
            ->setAllowedValues('comparison', ['=', '<>'])
            ->setDefaults(['comparison', '=']);
    }
    
    public function filter(QueryBuilder $queryBuilder, array $paths, string $identifier, $value): void
    {
        $identifier = $this->parseIdentifier($identifier); //parses identifier to :identifier
        
        $clause = sprintf('%s %s %s', 
            $paths[0], ComparisonQueryType merges paths with CONCAT_WS function
            $this->getOption('comparison'), 
            $identifier
        );
        
        $queryBuilder->andWhere($clause)
            ->setParameter($identifier, $value);
    }
}
````