# Lister Bundle

[![Scrutinizer Build Status](https://img.shields.io/scrutinizer/build/g/povs/ListerBundle/master?label=scrutinizer-ci)](https://scrutinizer-ci.com/g/povs/ListerBundle/build-status/master)
[![Travis Build Status](https://img.shields.io/travis/povs/ListerBundle/master?label=travis-ci)](https://travis-ci.com/povs/ListerBundle)
[![Code Coverage](https://scrutinizer-ci.com/g/povs/ListerBundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/povs/ListerBundle/?branch=master)
[![Code Quality](https://img.shields.io/scrutinizer/quality/g/povs/ListerBundle/master)](https://scrutinizer-ci.com/g/povs/ListerBundle/?branch=master)

### [Documentation](https://povs.github.io/ListerBundle)

Lister bundle helps to simplify and standardize data listing with pagination, filtering, sorting and more.

It compacts all list information into a single class which is easy to understand, extend and use.
 
By default library ships with two list types:
 - ArrayListType - can return data as array or json (paginated or not)
 - CsvListType - returns data as string separated by delimiter via streamed response.
 
However it is very easy to build your own list type for various needs.
 
For full stack web applications with `twig` consider using [ListerTwigBundle](https://github.com/povs/ListerTwigBundle)
which provides twig and ajax list types with various themes which can be modified.

#### Requirements

- Php >= `7.1`
- Symfony >= `4`
- Doctrine ORM