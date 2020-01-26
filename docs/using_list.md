# Using lists

`ListerInterface` is responsible for building lists and generating data depending on provided list type

It has three methods:
 - BuildList(string $list, string $type, array $parameters = []): self
   - `$list` fully qualified list class name
   - `$type` [list type](types/list.md) name
   - `$parameters` array that is passed to list `setParameters` method 
   - Will return self
 - generateResponse(array $options = []): Response
   - `$options` array that is passed to [list type](types/list.md) `generateOptions` method
   - Will return `Symfony\Component\HttpFoundation\Response`
 - generateData(array $options = [])
   - `$options` array that is passed to [list type](types/list.md) `generateData` method
   - Can return anything (i.e. has no strict return type)
   
Example usage:

```` php 
use Povs\ListerBundle\Declaration\ListerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SomeClass
{
    private $lister;

    //If u're not using autowire inject it with alias 'povs.lister'
    public function __construct(ListerInterface $lister)
    {
        $this->lister = $lister;
    }

    //By default configuration will return json
    public function listResponse(): Response
    {
        return $this->lister->buildList(MyList::class, 'list')
            ->generateResponse();
    }

    //Considering that generateData method of list type with name list will return array
    public function listArray(): array
    {
        return $this->lister->buildList(MyList::class, 'list')
            ->generateData();
    }

    //By default configuration will return csv via streamed response
    public function export(): StreamedResponse
    {
        return $this->lister->buildList(MyList::class, 'export')
            ->generateResponse();
    }
````