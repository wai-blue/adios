<?php

namespace ADIOS\Core\Db\Column;

class Varchar extends \ADIOS\Core\Db\Column
{

  protected string $type = 'varchar';
  protected array $enumValues = [];
  protected int $byteSize = 255;

  public function __construct(\ADIOS\Core\Model $model, string $title, int $byteSize = 255)
  {
    parent::__construct($model, $title);
    $this->byteSize = $byteSize;
  }

  public function getByteSize(): int { return $this->byteSize; }
  public function setByteSize(int $byteSize): Varchar { $this->byteSize = $byteSize; return $this; }

  public function getEnumValues(): array { return $this->enumValues; }
  public function setEnumValues(array $enumValues): Varchar { $this->enumValues = $enumValues; return $this; }

  public function jsonSerialize(): array
  {
    $column = parent::jsonSerialize();
    $column['byteSize'] = $this->byteSize;
    if (count($this->enumValues) > 0) $column['enumValues'] = $this->enumValues;
    return $column;
  }

  public function sqlCreateString(string $table, string $columnName): string
  {
    return "`{$columnName}` varchar($this->byteSize) " . $this->getRawSqlDefinition();
  }

}