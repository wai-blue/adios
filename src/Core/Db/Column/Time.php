<?php

namespace ADIOS\Core\Db\Column;

class Time extends \ADIOS\Core\Db\Column
{

  protected string $type = 'time';
  protected string $sqlDataType = 'time';

  public function normalize(mixed $value): mixed
  {
    return strtotime((string) $value) < 1000 ? null : $value;
  }

  public function sqlIndexString(string $table, string $columnName): string
  {
    return "index `{$columnName}` (`{$columnName}`)";
  }

}