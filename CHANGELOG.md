# Hubleto CHANGELOG

## Release v1.7 (not released yet)

  * enhanced type safety (thanks to PHPStan)
  * protected property `ADIOS\Core\Auth->user` and new getter methods (`getUser`, `getUserId`, `getUserLanguage`, `getUserRoles`)
  * new style of column description using `ADIOS\Core\Db\Column` objects
  * new method `Model->columnDescribe()` to support concept of descriptions for columns, similar to `tableDescribe()` and `formDescribe()`
  * new classes `\ADIOS\Core\Db\ColumnProperty` and `\ADIOS\Core\Db\ColumnProperty\Autocomplete`
  * type-safe definition of columns in the model:

```php
  'class' => (new \ADIOS\Core\Db\Column\Varchar($this, 'Class'))
    ->setProperty('autocomplete', (new \ADIOS\Core\Db\ColumnProperty\Autocomplete())->setEndpoint('api/classes/get')->setCreatable(true))
```

## Release v1.6

First version in the changelog