<?php

namespace ADIOS\Core\Db\Column;

class Boolean extends \ADIOS\Core\Db\Column
{

  protected string $type = 'boolean';
  protected string $sqlDataType = 'int(1)';
  protected string $noValue = 'N';

  public function getYesValue(): string { return $this->yesValue; }
  public function setYesValue(string $yesValue): Lookup { $this->yesValue = $yesValue; return $this; }

  public function getNoValue(): string { return $this->noValue; }
  public function setNoValue(string $noValue): Lookup { $this->noValue = $noValue; return $this; }

  public function __construct(\ADIOS\Core\Model $model, string $title)
  {
    parent::__construct($model, $title);
  }

  public function normalize(mixed $value): mixed
  {
    if (empty($value) || !((bool) $value) || $value === $this->getNoValue()) {
      return $columnDescription['noValue'] ?? 0;
    } else {
      return $columnDescription['yesValue'] ?? 1;
    }
  }

  public function sqlIndexString(string $table, string $columnName): string
  {
    return "index `{$columnName}` (`{$columnName}`)";
  }

}