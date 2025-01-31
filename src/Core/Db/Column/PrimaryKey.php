<?php

namespace ADIOS\Core\Db\Column;

class PrimaryKey extends \ADIOS\Core\Db\Column
{

  protected string $type = 'int';
  protected int $byteSize = 255;
  protected string $rawSqlDefinition = 'primary key auto_increment';
  protected bool $readonly = true;

  public function __constructor(\ADIOS\Core\Db $db, string $title, int $byteSize = 255)
  {
    parent::__constructor($db, $title);
    $this->byteSize = $byteSize;
  }

  public function getByteSize(): int
  {
    return $this->byteSize;
  }

  public function setByteSize(int $byteSize): Autocomplete
  {
    $this->byteSize = $byteSize;
    return $this;
  }

  public function jsonSerialize(): array
  {
    $column = parent::jsonSerialize();
    $column['byteSize'] = $this->byteSize;
    return $column;
  }

}