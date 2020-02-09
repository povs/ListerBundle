# List types
List types converts [view](/views.md) object to response or any other data type (e.g. array)

## Available List types

### Array List type
Array list type returns data as json on array.
 - `generateResponse` returns data as JsonResponse
 - `generateData` returns data as array

*available settings*:

Name | type | default | description
--- | --- | --- | ---
length | int | 100 | how much to show per page (if paginated) or query batch
limit | int | 0 | Max results that can be fetched ( 0 - unlimited)
paged | bool | true | Whether results should be paged.
    
### Csv List type
Csv list type returns data as StreamedResponse with csv content
 - `generateResponse` returns data as StreamedResponse
 - `generateData` returns null
 
*available settings*:

Name | type | default | description
--- | --- | --- | ---
length | int | 10000 | query batch
file_name | string | not set | file name, it's required
delimiter | delimiter | , | CSV delimiter
limit | int | 0 | Max results that can be fetched ( 0 - unlimited)


## Creating your own list type
Lets say ArrayListType is not available by default and we would want to build it.
Full example of already built in array list type can be found `Povs\ListerBundle\Type\ListType\ArrayListType`

First of all we create a new `ListType` class which extends `Povs\ListerBundle\Type\ListType\AbstractListType`

```php
namespace App\Lister\ListType;

use Povs\ListerBundle\Type\ListType\AbstractListType;

class ArrayListType extends AbstractListType
{
    //...
}
```

To configure settings of the list type `configureSettings` method is used.
Those settings are set via `type_configuration` array in `list_configuration`.
Settings are configured via Symfony Options resolver component

```php
public function configureSettings(OptionsResolver $optionsResolver): void
{
    $optionsResolver->setDefined(['length', 'limit', 'paged']);
    $optionsResolver->setRequired(['length', 'limit', 'paged']);
    $optionsResolver->setAllowedTypes('length', 'int');
    $optionsResolver->setAllowedTypes('limit', 'int');
    $optionsResolver->setAllowedTypes('paged', 'bool');
    $optionsResolver->setDefaults([
        'length' => 100,
        'limit' => 0,
        'paged' => true
    ]);
}
```

Next we want to tell by what `length` and `page` to fetch data.
In each of those methods value from request is passed.
 
```php
public function getLength(?int $length): int
{
    if (false === $this->config['paged'] || null === $length) {
        $length = $this->config['length'];
    }

    return $length;
}

public function getCurrentPage(?int $currentPage): int
{
    if (false === $this->config['paged'] || null === $currentPage) {
        $currentPage = 1;
    }

    return $currentPage;
}
```

If some additional options are required, those are passed when building list.
To configure those options use `configureOptions` method. 

```php
public function configureOptions(OptionsResolver $optionsResolver): void
{
    $optionsResolver->setDefined(['additional_option']);
    $optionsResolver->setRequired(['additional_option']);
}
```
Setting options..
```php
// Some Controller

public function list()
{
    return $this->lister->buildList(MyList::class, 'list')
        ->generateResponse(['additional_option' => 'foo']);

}
```

To return data `generateResponse` and `generateData` methods are used.
To both of those methods [ListView object](/views.md) and array of options are passed.

```php
// Convert ListView to data array
public function generateData(ListView $listView, array $options): array
{
    $batch = 0;
    $data = [
        'data' => [],
        'total' => $listView->getPager()->getTotal()
    ];

    foreach ($listView->getBodyRows($this->config['paged']) as $row) {
        $data['data'][] = $row->getLabeledValue();
        
        if (0 !== $this->config['limit'] && ++$batch === $this->config['limit']) {
            break;
        }
    }
    
    return $data;
}

// return that array as Json Response
public function generateResponse(ListView $listView, array $options): Response
{
    return new JsonResponse($this->generateData(ListView $listView, array $options));
}
```