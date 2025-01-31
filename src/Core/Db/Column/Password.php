<?php

namespace ADIOS\Core\Db\Column;

class Password extends \ADIOS\Core\Db\Column
{

  protected string $type = 'password';
  protected int $byteSize = 255;
  protected bool $hidden = true;

  public function __construct(\ADIOS\Core\Model $model, string $title, int $byteSize = 255)
  {
    parent::__construct($model, $title);
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