<?php

namespace ADIOS\Core\Db\Column;

class Lookup extends \ADIOS\Core\Db\Column
{

  protected string $type = 'lookup';
  protected string $lookupModel = '';

  protected bool $disableForeignKey = false;
  protected string $foreignKeyColumn = 'id';
  protected string $foreignKeyOnDelete = 'RESTRICT';
  protected string $foreignKeyOnUpdate = 'RESTRICT';

  public function __construct(\ADIOS\Core\Model $model, string $title, string $lookupModel = '')
  {
    parent::__construct($model, $title);
    $this->lookupModel = $lookupModel;
  }

  public function getLookupModel(): string { return $this->lookupModel; }
  public function setLookupModel(string $lookupModel): Lookup { $this->lookupModel = $lookupModel; return $this; }

  public function jsonSerialize(): array
  {
    $column = parent::jsonSerialize();
    $column['model'] = $this->lookupModel;
    return $column;
  }

}