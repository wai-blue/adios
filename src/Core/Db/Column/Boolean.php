<?php

namespace ADIOS\Core\Db\Column;

class Boolean extends \ADIOS\Core\Db\Column
{

  protected string $type = 'boolean';
  protected string $sqlDataType = 'int(1)';

  public function __construct(\ADIOS\Core\Model $model, string $title)
  {
    parent::__construct($model, $title);
  }


  public function normalize(mixed $value): mixed
  {
    return (bool) $value;
  }
  public function sqlIndexString(string $table, string $columnName): string
  {
    return "index `{$columnName}` (`{$columnName}`)";
  }

}