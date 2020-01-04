# Filter types

With filter types you can set default options so you won't have to repeat it on every list.
All filter types must implement `Povs\ListerBundle\Type\FilterType\FilterTypeInterface`
This interface has only one method: `getDefaultOptions(): array`

##Creating your own filter type

Lets say we want to build a simple text filter type which will filter using mysql `LIKE` with wildcards i.e. `LIKE '%value%'`.

````php 
namespace App\Lister\FilterType;

use Povs\ListerBundle\Type\FilterType\FilterTypeInterface;
use Povs\ListerBundle\Type\QueryType\ComparisonQueryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class LikeFilterType implements FilterTypeInterface
{
    public function getDefaultOptions(): array
    {
        return [
            'query_type' => ComparisonQueryType::class,
            'query_options' => [
                'type' => ComparisonQueryType::COMPARISON_LIKE,
                'wildcard' => ComparisonQueryType::WILDCARD,
            ],
            'input_type' => TextType::class,
            'input_options' => ['label' => 'Search']
        ];
    }
}
```` 

To use this filter type just pass it to `$filterMapper->add()` as a second argument
````php 

// Your list
public function buildFilterFields(FilterMapper $filterMapper): void
{
    $filterMapper->add('user', LikeFilterType::class, [
        'label' => 'User', 
        'path' => ['user.firstName', 'user.lastName']
    ])
}
````