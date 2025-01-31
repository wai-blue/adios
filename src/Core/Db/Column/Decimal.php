<?php

namespace ADIOS\Core\Db\Column;

class Decimal extends \ADIOS\Core\Db\Column
{

  protected string $type = 'float';
  protected int $byteSize = 14;
  protected int $decimals = 4;

  public function __construct(\ADIOS\Core\Model $model, string $title, int $byteSize = 14, int $decimals = 4)
  {
    parent::__construct($model, $title);
    $this->byteSize = $byteSize;
    $this->decimals = $decimals;
  }

  public function getByteSize(): int { return $this->byteSize; }
  public function setByteSize(int $byteSize): Autocomplete { $this->byteSize = $byteSize; return $this; }

  public function getDecimals(): int { return $this->decimals; }
  public function setDecimals(int $decimals): Autocomplete { $this->decimals = $decimals; return $this; }

  public function jsonSerialize(): array
  {
    $column = parent::jsonSerialize();
    $column['byteSize'] = $this->byteSize;
    $column['decimals'] = $this->decimals;
    return $column;
  }

}