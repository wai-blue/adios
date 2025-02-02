<?php

namespace ADIOS\Core\Db\Column;

class Decimal extends \ADIOS\Core\Db\Column
{

  protected string $type = 'float';
  protected string $sqlDataType = 'decimal';
  protected int $byteSize = 14;
  protected int $decimals = 4;

  public function __construct(\ADIOS\Core\Model $model, string $title, int $byteSize = 14, int $decimals = 4)
  {
    parent::__construct($model, $title);
    $this->byteSize = $byteSize;
    $this->decimals = $decimals;
  }

  public function getByteSize(): int { return $this->byteSize; }
  public function setByteSize(int $byteSize): Decimal { $this->byteSize = $byteSize; return $this; }

  public function getDecimals(): int { return $this->decimals; }
  public function setDecimals(int $decimals): Decimal { $this->decimals = $decimals; return $this; }

  public function jsonSerialize(): array
  {
    $column = parent::jsonSerialize();
    $column['byteSize'] = $this->byteSize;
    $column['decimals'] = $this->decimals;
    return $column;
  }

  public function normalize(mixed $value): mixed
  {
    return (float) $value;
  }

  public function validate(mixed $value): bool
  {
    return empty($value) || is_numeric($value);
  }

  public function sqlCreateString(string $table, string $columnName): string
  {
    return (empty($this->sqlDataType) ? '' : "`{$columnName}` {$this->sqlDataType}($this->byteSize, $this->decimals) " . $this->getRawSqlDefinition());
  }

}