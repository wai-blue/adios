<?php

namespace ADIOS\Core\Db\Column;

use \ADIOS\Core\Db\ColumnProperty\Autocomplete;

class Lookup extends \ADIOS\Core\Db\Column
{

  protected string $type = 'lookup';
  protected string $sqlDataType = 'int(8)';
  protected string $lookupModel = '';
  protected string $rawSqlDefinition = 'NULL default 0';

  protected bool $disableForeignKey = false;
  protected string $foreignKeyColumn = 'id';
  protected string $foreignKeyOnDelete = 'RESTRICT';
  protected string $foreignKeyOnUpdate = 'RESTRICT';
  protected ?Autocomplete $autocomplete = null;

  public function __construct(\ADIOS\Core\Model $model, string $title, string $lookupModel = '', string $foreignKeyBehaviour = 'RESTRICT')
  {
    parent::__construct($model, $title);
    $this->lookupModel = $lookupModel;
    $this->foreignKeyOnDelete = $foreignKeyBehaviour;
    $this->foreignKeyOnUpdate = $foreignKeyBehaviour;
  }

  public function getLookupModel(): string { return $this->lookupModel; }
  public function setLookupModel(string $lookupModel): Lookup { $this->lookupModel = $lookupModel; return $this; }

  public function setFkOnDelete(string $fkOnDelete): Lookup { $this->foreignKeyOnDelete = $fkOnDelete; return $this; }
  public function setFkOnUpdate(string $fkOnUpdate): Lookup { $this->foreignKeyOnUpdate = $fkOnUpdate; return $this; }

  public function getAutocomplete(): Autocomplete { return $this->autocomplete; }
  public function setAutocomplete(Autocomplete $autocomplete): Varchar { $this->autocomplete = $autocomplete; return $this; }

  public function describeInput(): \ADIOS\Core\Description\Input
  {
    $description = parent::describeInput();
    if (!empty($this->getLookupModel())) $description->setLookupModel($this->getLookupModel());
    return $description;
  }

  public function jsonSerialize(): array
  {
    $column = parent::jsonSerialize();
    $column['model'] = $this->lookupModel;
    $column['foreignKeyOnDelete'] = $this->foreignKeyOnDelete;
    $column['foreignKeyOnUpdate'] = $this->foreignKeyOnUpdate;
    if ($this->autocomplete !== null) $column['autocomplete'] = $this->autocomplete;
    return $column;
  }

  public function normalize(mixed $value): mixed
  {
    if ($value === 0) {
      return null;
    } if (is_numeric($value)) {
      return ((int) $value) <= 0 ? 0 : (int) $value;
    } else if ($value['_isNew_'] ?? false) {
      $lookupModel = $this->model->app->getModel($this->model->getColumns()[$colName]->getLookupModel());
      return $lookupModel->eloquent->create($lookupModel->getNewRecordDataFromString($value['_LOOKUP'] ?? ''))->id;
    } else if (empty($value)) {
      return null;
    }
  }

  public function sqlIndexString(string $table, string $columnName): string
  {
    return "index `{$columnName}` (`{$columnName}`)";
  }

}