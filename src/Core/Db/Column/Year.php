<?php

namespace ADIOS\Core\Db\Column;

class Year extends \ADIOS\Core\Db\Column
{

  protected string $type = 'year';
  protected string $sqlDataType = 'year';

  public function normalize(mixed $value): mixed
  {
    return (int) $value;
  }

  public function sqlIndexString(string $table, string $columnName): string
  {
    return "index `{$columnName}` (`{$columnName}`)";
  }

}