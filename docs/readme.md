# About

Lister bundle helps to simplify and standardize data listing with pagination, filtering, sorting and more.

It compacts all list information into a single class which is easy to understand, extend and use.

Lister bundle can be configurable with custom types:
 - [ListType](types/list.md) which configures response format (i.e. array, csv, json, html, xml..)
 - [FieldType](types/field.md) to help build commonly used fields across lists
 - [FilterType](types/filter.md) to help build commonly used filters
 - [QueryType](types/query.md) to customise how data should be filtered
 - [SelectorType](types/selector.md) to customise how data should be fetched from the database
 
By default library ships with two list types:
 - ArrayListType - can return data as array or json (paginated or not)
 - CsvListType - returns data as string separated by delimiter via streamed response.
 
However it is very easy to build your own list type for various needs. More about it here [ListTypes](types/list.md).
 
For full stack web applications with `twig` consider using [ListerTwigBundle](https://github.com/povs/ListerTwigBundle)
which provides twig and ajax list types with various themes which can be modified.
 
 

