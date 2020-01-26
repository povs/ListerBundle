# Registering your types

If you're using the [default services.yaml configuration](https://symfony.com/doc/current/service_container.html#service-container-services-load-example), you're done! 
It will automatically catch all types and tags it with an appropriate tag.

Otherwise types has to be registered as services and tagged by it's type:
 - List type: `povs_lister.list_type`
 - Field type: `povs_lister.field_type`
 - Filter type: `povs_lister.filter_type`
 - Query type: `povs_lister.query_type`
 - Selector type: `povs_lister.selector_type`

Example
```yaml
# config/services.yaml
services:
    App\Lister\ListType\MyListType:
        tags: ['povs_lister.list_type']
```
