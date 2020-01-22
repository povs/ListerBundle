# Views

Views are objects generated with various list information to access its value, filters, etc...
Views are passed to `list types` which parses it to required response.

## ListView

List view is a parent object which contains all other views:

Available methods:

```` php
//Will return RowView with header values aka labels
$listView->getHeaderRow();

//Will return iterable of RowViews objects with data values. 
//Param paged - whether results should be paged. If false - all results will be returned. (not recommended with big data)
$listView->getBodyRows(true);

//Will return paged object
$listView->getPager();

//Will return symfony form object with filter.
$listView->getFilter();

//Will return router object
$listView->getRouter();
````

## RowView
Contains header or body data values

Available methods:

```` php 
//Returns iterable of FieldView objects
$rowView->getFields();

//Returns id of current row (null for HeaderRow)
$rowView->getId();

//Returns array of row values
$rowView->getValue();

//Returns array of row values where indexes are field labels.
$rowView->getLabeledValue();

//Returns parent ListView
$rowView->getList();
````

## FieldView

Contains various field information including its value.

Available methods:

```` php 
//Returns field value
$fieldView->getValue();

//Returns field id
$fieldView->getId();

//Returns field option value from 'view_options'
$fieldView->getOption('option_name');

//Returns bool whether field has an option in 'view_options'
$fieldView->hasOption('option_name');

//Returns field label
$fieldView->getLabel();

//Returns whether field is sortable
$fieldView->isSortable();

//Returns sort direction or null
$fieldView->getSort();

//Returns parent RowView object
$fieldView->getRow();
````


## PagerView

PagerView is responsible for fetching data from database and accessing various pagination related information.
It can also be used to iterate between pages.

Available methods:

```` php 
//Returns array of data by current page and length
$pagerView->getData();

//Returns current length
$pagerView->getLength();

//Returns total count of results
$pagerView->getTotal();

//Returns current page
$pagerView->getCurrentPage();

//Returns next page from current page. Null if current page is the last page
$pagerView->getNextPage();

//Returns prev page from current page. Null if current page is first page
$pagerView->getPrevPage();

//Validates whether passed page is valid.
$pagerView->validatePage(1);

//Creates pages array for rendering it in front.
$pagerView->getPages();

//Iterates page to provided page number. Returns bool whether iteration was successful.
//If page iterated successfully you can get new data by $pagerView->getData() method.
$pagerView->iteratePage(5);

//Iterates page to the next one. Returns bool whether iteration was successful.
//If page iterated successfully you can get new data by $pagerView->getData() method.
$pagerView->iterateNextPage();

//Gets first result of provided page. If null - current page will be taken.
$pagerView->getFirstResult(5);

//Gets last result of provided page. If null - current page will be taken.
$pagerView->getLastResult(5);
````

## RouterView

Router view is responsible for generating various list routes: page iteration, sorting, filtering, etc.

Available methods:

```` php 
//Returns sort route by field id and it's direction
$routerView->getSortRoute('field_id', 'desc');

//Returns length route by provided length
$routerView->getLengthRoute(50);

//Returns page route by provided page number
$routerView->getPageRoute(2);

//Used to generate custom routes.
//It accepts two parameters:
//array $options (Route options)
//bool $merge whether to merge options with current request or overwrite them
$routerView->generate([], true);

//Returns current request route without any options. Used as filter action route
$routerView->getRoute();

//Returns request name from config by its value
$routerView->getRequestName('name');
````