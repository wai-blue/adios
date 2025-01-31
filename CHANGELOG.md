# Hubleto CHANGELOG

## Release v1.7 (not released yet)

  * enhanced type safety (thanks to PHPStan)
  * protected property `ADIOS\Core\Auth->user` and new getter methods (`getUser`, `getUserId`, `getUserLanguage`, `getUserRoles`)
  * new method `Model->columnDescribe()` to support concept of descriptions for columns, similar to `tableDescribe()` and `formDescribe()`
  * new classes `\ADIOS\Core\Db\ColumnProperty` and `\ADIOS\Core\Db\ColumnProperty\Autocomplete`
  * *varchar* column can have new property `autocomplete`:

```php
  'class' => [
    'type' => 'varchar',
    'title' => 'Class',
    'autocomplete' => (new \ADIOS\Core\Db\ColumnProperty\Autocomplete($this))->setEndpoint('api/classes/get'),
  ],
```

## Release v1.6

First version in the changelog