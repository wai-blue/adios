<?php

namespace ADIOS\Core\Db\Column;

class Lookup extends \ADIOS\Core\Db\Column
{

  protected string $type = 'lookup';
  protected string $model = '';

  protected bool $disableForeignKey = false;
  protected string $foreignKeyColumn = 'id';
  protected string $foreignKeyOnDelete = 'RESTRICT';
  protected string $foreignKeyOnUpdate = 'RESTRICT';

  public function __constructor(\ADIOS\Core\Db $db, string $title, string $model = '')
  {
    parent::__constructor($db, $title);
    $this->model = $model;
  }

  public function getModel(): string { return $this->model; }
  public function setModel(string $model): Lookup { $this->model = $model; return $this; }

  public function jsonSerialize(): array
  {
    $column = parent::jsonSerialize();
    $column['model'] = $this->model;
    return $column;
  }

}