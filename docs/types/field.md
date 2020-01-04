#Field types

Field types are used to customize value returned from selector type.
Every field type has to implement `Povs\ListerBundle\Type\FieldType\FieldTypeInterface`
This interface has two methods:

`getValue($value, string $type, array $options)` where 
 - $value - value fetched from DB
 - $type - list type 
 - $options - options passed via 'field_type_options' option when adding Field
 
 `getDefaultOptions(string $type)` where 
 - $type - list type
 
 > with `getDefaultOptions` method you can pass any `ListField` option. Those options can still be overwritten when building list.

##Creating your own field type
As an example lets build UserFieldType.

Lets say User entity has firstName, lastName properties and every time we are adding user to the list we want to render it as a full name.

````php 
namespace App\Lister\FieldType;

use Povs\ListerBundle\Type\FieldType\FieldTypeInterface;

class UserFieldType implements FieldTypeInterface
{
    public function getValue($value, string $type, array $options)
    {
        return sprintf('%s %s', $value['first'], $value['last']);
    }

    public function getDefaultOptions(string $type): array
    {
        return [
            'label' => 'Full name',
            'property' => ['first' => firstName', 'last' => 'lastName'],
            'lazy' => $type === 'list' ? true : false
        ];
    }
}
````

To use this type pass it as a second argument to `listMapper->add()`

````php 

// Your list
public function buildListFields(ListMapper $listMapper): void
{
    $listMapper->add(
        'user', //Path to the user (this will generate paths ['user.firstName', 'user.lastName']) 
        UserFieldType::class, //FullyQualifiedName of the field type
        ['label' => 'User'] //Options (label is overwritten here)
    )
}
````

> If your data class is User just add option `path => ''` so it will generate paths `['firstName', 'lastName']` (without user reference)