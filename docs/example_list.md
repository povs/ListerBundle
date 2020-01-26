# Examples

## Example list

For this example lets say we have two entities:

```
    User:
        firstName
        lastName
        email
        address (reference to address)

    Address:
        Country        
        streetName
        houseNumber
        
```

Example list:

``` php
<?php

namespace App\Lister;

use App\Entity\User;
use Povs\ListerBundle\Declaration\AbstractList;
use Povs\ListerBundle\Mapper\FilterMapper;
use Povs\ListerBundle\Mapper\ListMapper;
use Povs\ListerBundle\Type\QueryType\ComparisonQueryType;
use Povs\ListerBundle\Type\QueryType\ContainsQueryType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UsersList extends AbstractList
{
    //Configures list. Overwrites export list type parameter - file_name.
    public function configure(): array
    {
        return [
            'types_configuration' => [
                'export' => [
                    'file_name' => 'Users'
                ]
            ]
        ];
    }

    //Builds list fields for list type with name 'list'. Required
    public function buildListFields(ListMapper $listMapper): void
    {
        $listMapper->add('firstName', null, ['label' => 'First name'])
            ->add('lastName', null, ['label' => 'Last name'])
            ->add('mail', null, ['label' => 'Mail address'])
            ->add('address.country', null, ['label' => 'Country'])
            ->add('address.streetName', null, ['label' => 'Street name'])
            ->add('address.houseNumber', null, ['label' => 'House number']);
    }

    //Removes address.houseNumber field in export list type
    //Fields configuration naming scheme: build{typeName}Fields.
    //So for example if we had list type with name xml - we could configure it with buildXmlFields method
    //Methods like this are not required. If they're not overwritten all fields will be taken from 'buildListFields' method by default
    //Overwrite fields for other list types only when changes are needed.
    public function buildExportFields(ListMapper $listMapper): void
    {
        $listMapper->build() //Copies fields form buildListFields method
            ->remove('address.houseNumber'); //removes houseNumber field
    }

    //Builds filter fields for this list
    //In this example list will be filtered by fullName (i.e. firstName and lastName) and country name
    public function buildFilterFields(FilterMapper $filterMapper): void
    {
        $filterMapper
            //Filters users by firstName and lastName.
            //It is recommended to use FilterTypes for cases like (e.g. LikeFilterType)
            ->add('fullName', null, [
                'query_type' => ComparisonQueryType::class,
                'query_options' => [
                    'type' => ComparisonQueryType::COMPARISON_LIKE,
                    'wildcard' => ComparisonQueryType::WILDCARD
                ],
                'input_type' => TextType::class,
                'input_options' => ['label' => 'FirstName'],
                'paths' => ['firstName', 'lastName']
            ])
            ->add('address.country', null, [ //Filters users by country
                'query_type' => ContainsQueryType::class,
                'input_type' => ChoiceType::class,
                'input_options' => [
                    'multiple' => true,
                    'choices' => [] //Countries
                ]
            ]);
    }

    //Returns fully qualified name of base entity class
    public function getDataClass(): string
    {
        return User::class;
    }
}
```

## Extending lists

Lets say we want another list where only basic user information should be listed. To do so we can simple extend already created UserList:

``` php 
<?php

namespace App\Lister;

use Povs\ListerBundle\Mapper\FilterMapper;
use Povs\ListerBundle\Mapper\ListMapper;

class BasicUsersList extends UsersList
{
    public function buildListFields(ListMapper $listMapper): void
    {
        parent::buildListFields($listMapper); // Builds all fields from the parent
        
        //Removes all fields that are not required in this list
        $listMapper->remove('address.country')
            ->remove('address.streetName')
            ->remove('address.houseNumber');
    }
    
    //Just copies all fields from buildListFields method.
    public function buildExportFields(ListMapper $listMapper): void
    {
        $listMapper->build();
    }

    //Removes address filter from parent list since address is not listed here
    public function buildFilterFields(FilterMapper $filterMapper): void
    {
        $filterMapper->remove('address.country');
    }
}
```

