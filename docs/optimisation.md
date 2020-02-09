# List optimisation

## Lazy field

Making field lazy loadable is very easy, just pass `lazy` option as a true when adding a field.

```php
$listMapper->add('property', null, ['label' => 'My Label', 'lazy' => true]);
```

When field is marked as lazy it will fetch it by seperate query on each row.

> All fields that are marked as lazy are fetched with a single query.
> Meaning if three fields are marked as lazy and two as non lazy - two fields will be fetched with initial query (all results)
> And other three will be fetched with a single query on every row iteration

### When to use lazy field?

On big tables with paginated results.

Especially when list query contains [filter](types/filter.md) or [selector](types/selector.md) types which has aggregation.
Or with tables which has bad optimisations with a lot of joins.

> If list has no filter or selector types with aggregation `DISTINCT` keyword will be used instead of `GROUP BY` to fetch only unique results. 

### When not to use lazy field?

Fields should be marked as lazy only on lists which will be **paginated**. 
It's not recommended to use it with list types like `export` which will fetch all rows from the table.


