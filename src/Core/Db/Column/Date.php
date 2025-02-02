<?php

namespace ADIOS\Core\Db\Column;

class Date extends \ADIOS\Core\Db\Column
{

  protected string $type = 'date';
  protected string $sqlDataType = 'date';

  public function normalize(mixed $value): mixed
  {
    return strtotime((string) $value) < 1000 ? null : $value;
  }

  public function sqlIndexString(string $table, string $columnName): string
  {
    return "index `{$columnName}` (`{$columnName}`)";
  }

}