# Hubleto CHANGELOG

## Release 1.8 (not published yet)

  * new method `Loader->urlParamNotEmpty()`
  * various bugfixes
  * description API is now able to describe inputs

## Release v1.7

  * enhanced type safety (thanks to PHPStan)
  * removed obsolete `ADIOS\Core\Loader->db` object (use safer `ADIOS\Core\Loader->pdo` for database operations)
  * protected property `ADIOS\Core\Auth->user` and new getter methods (`getUser`, `getUserId`, `getUserLanguage`, `getUserRoles`)
  * new style of column description using `ADIOS\Core\Db\Column` objects
  * new method `Model->describeInput()` to support concept of descriptions for columns, similar to `describeTable()` and `describeForm()`
  * new classes `\ADIOS\Core\Db\ColumnProperty` and `\ADIOS\Core\Db\ColumnProperty\Autocomplete`
  * type-safe definition of columns in the model:

```php
  'class' => (new \ADIOS\Core\Db\Column\Varchar($this, 'Class'))
    ->setProperty('autocomplete', (new \ADIOS\Core\Db\ColumnProperty\Autocomplete())->setEndpoint('api/classes/get')->setCreatable(true))
```

## Release v1.6

First version in the changelog