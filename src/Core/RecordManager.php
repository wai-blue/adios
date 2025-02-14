<?php

namespace ADIOS\Core;

/**
  * Record-management
  * CRUD-like layer for manipulating records (data)
*/

class RecordManager {

  protected \ADIOS\Core\Loader $app;
  protected \ADIOS\Core\Model $model;

  /** What relations to be included in loaded record. If null, default relations will be selected. */
  /** @property array<string> */
  protected array $relationsToRead = [];

  protected int $maxReadLevel = 2;

  public function __construct(\ADIOS\Core\Model $model) {
    $this->model = $model;
    $this->app = $model->app;
  }

  public function getRelationsToRead(): array
  {
    return $this->relationsToRead;
  }

  public function setRelationsToRead(array $relationsToRead)
  {
    $this->relationsToRead = $relationsToRead;
  }

  public function getMaxReadLevel(): array
  {
    return $this->maxReadLevel;
  }

  public function setMaxReadLevel(array $maxReadLevel)
  {
    $this->maxReadLevel = $maxReadLevel;
  }

  /**
   * prepareRead
   * @param mixed $query Leave empty for default behaviour.
   * @param int $level Leave empty for default behaviour.
   * @return mixed Eloquent query used to read record.
   */
  public function prepareRead(mixed $query = null, int $level = 0): mixed
  {
    if ($query === null) $query = $this->model->eloquent;

    $selectRaw = [];
    $withs = [];
    $joins = [];

    foreach ($this->model->getColumns() as $colName => $column) {
      $colDefinition = $column->toArray();
      if ((bool) ($colDefinition['hidden'] ?? false)) continue;
      $selectRaw[] = $this->model->table . '.' . $colName;

      if (isset($colDefinition['enumValues']) && is_array($colDefinition['enumValues'])) {
        $tmpSelect = "CASE";
        foreach ($colDefinition['enumValues'] as $eKey => $eVal) {
          $tmpSelect .= " WHEN `{$this->model->table}`.`{$colName}` = '{$eKey}' THEN '{$eVal}'";
        }
        $tmpSelect .= " ELSE '' END AS `_ENUM[{$colName}]`";

        $selectRaw[] = $tmpSelect;
      }
    }

    $selectRaw[] = $level . ' as _LEVEL';
    $selectRaw[] = '(' . str_replace('{%TABLE%}', $this->model->table, $this->model->getLookupSqlValue()) . ') as _LOOKUP';

    // LOOKUPS and RELATIONSHIPS
    foreach ($this->model->getColumns() as $columnName => $column) {
      $colDefinition = $column->toArray();
      if ($colDefinition['type'] == 'lookup') {
        $lookupModel = $this->app->getModel($colDefinition['model']);
        $lookupConnection = $lookupModel->eloquent->getConnectionName();
        $lookupDatabase = $lookupModel->eloquent->getConnection()->getDatabaseName();
        $lookupTableName = $lookupModel->getFullTableSqlName();
        $joinAlias = 'join_' . $columnName;

        $selectRaw[] = "(" .
          str_replace("{%TABLE%}", $joinAlias, $lookupModel->getLookupSqlValue())
          . ") as `_LOOKUP[{$columnName}]`"
        ;

        $joins[] = [
          $lookupDatabase . '.' . $lookupTableName . ' as ' . $joinAlias,
          $joinAlias.'.id',
          '=',
          $this->model->table.'.'.$columnName
        ];
      }
    }

    // TODO: Toto je pravdepodobne potencialna SQL injection diera. Opravit.
    $query = $query->selectRaw(join(',', $selectRaw)); //->with($withs);
    foreach ($this->model->relations as $relName => $relDefinition) {
      if (count($this->relationsToRead) > 0 && !in_array($relName, $this->relationsToRead)) continue;

      $relModel = new $relDefinition[1]($this->app);

      if ($level <= $this->maxReadLevel) {
        $query->with([$relName => function($q) use($relModel, $level) {
          return $relModel->recordManager->prepareRead($q, $level + 1);
        }]);
      }
    }

    foreach ($joins as $join) {
      $query->leftJoin($join[0], $join[1], $join[2], $join[3]);
    }

    return $query;
  }

  function addFulltextSearch(mixed $query, string $search): void
  {
    if (!empty($search)) {
      foreach ($this->model->getColumns() as $columnName => $column) {
        $enumValues = $column->getEnumValues();
        if (count($enumValues) > 0) {
          $query->orHaving('_ENUM[' . $columnName . ']', 'like', "%{$search}%");
        } else if ($column->getType() == 'lookup') {
          $query->orHaving('_LOOKUP[' . $columnName . ']', 'like', "%{$search}%");
        } else {
          $query->orHaving($columnName, 'like', "%{$search}%");
        }
      }
    }
  }

  public function addColumnSearch(mixed $query, array $columnSearch): void
  {
    if (count($columnSearch) > 0) {
      // TODO
    }
  }

  public function addOrderBy(mixed $query, array $orderBy): void
  {
    if (isset($orderBy['field']) && isset($orderBy['direction'])) {
      $query->orderBy($orderBy['field'], $orderBy['direction']);
    }
  }

  public function paginate(mixed $query, int $itemsPerPage, int $page): array
  {
    $data = $query->paginate(
      $itemsPerPage,
      ['*'],
      'page',
      $page
    )->toArray();

    // Laravel pagination
    if (!is_array($data)) $data = [];
    if (!is_array($data['data'])) $data['data'] = [];

    return $data;
  }

  public function read(mixed $query): array {
    $record = $query->first()?->toArray();
    if (!is_array($record)) $record = [];

    $record = $this->encryptIds($record);
    $record['_RELATIONS'] = array_keys($this->model->relations);
    if (count($this->relationsToRead) > 0) {
      $record['_RELATIONS'] = array_values(array_intersect($record['_RELATIONS'], $this->relationsToRead));
    }

    return $record;
  }

  public function encryptIds(array $record): array
  {

    foreach ($this->model->getColumns() as $colName => $column) {
      $colDefinition = $column->toArray();
      if (($colName == 'id' || $colDefinition['type'] == 'lookup') && $record[$colName] !== null) {
        $record[$colName] = \ADIOS\Core\Helper::encrypt($record[$colName]);
      }
    }

    $record['_idHash_'] =  \ADIOS\Core\Helper::encrypt($record['id'] ?? '', '', true);

    return $record;
  }

  public function decryptIds(array $record): array
  {
    foreach ($this->model->getColumns() as $colName => $column) {
      $colDefinition = $column->toArray();
      if ($colName == 'id' || $colDefinition['type'] == 'lookup') {
        if (isset($record[$colName]) && $record[$colName] !== null && is_string($record[$colName])) {
          $record[$colName] = \ADIOS\Core\Helper::decrypt($record[$colName]);
        }
      }
    }

    foreach ($this->model->relations as $relName => $relDefinition) {
      if (!isset($record[$relName]) || !is_array($record[$relName])) continue;

      list($relType, $relModelClass) = $relDefinition;
      $relModel = new $relModelClass($this->app);

      switch ($relType) {
        case \ADIOS\Core\Model::HAS_MANY:
          foreach ($record[$relName] as $subKey => $subRecord) {
            $record[$relName][$subKey] = $relModel->recordManager->decryptIds($record[$relName][$subKey]);
          }
        break;
        case \ADIOS\Core\Model::HAS_ONE:
          $record[$relName] = $relModel->recordManager->decryptIds($record[$relName]);
        break;
      }
    }

    return $record;
  }

  public function create(array $record): array
  {
    unset($record['id']);
    $record['id'] = $this->model->eloquent->create($record)->id;
    return $record;
  }

  public function update(array $record): array
  {
    $this->model->eloquent->find((int) ($record['id'] ?? 0))->update($record);
    return $record;
  }

  public function delete(int|string $id): int
  {
    $this->model->eloquent->where('id', $id)->delete();
    return 1; // TODO: return $rowsAffected
  }

  public function save(array $record, int $idMasterRecord = 0): array
  {

    $id = (int) ($record['id'] ?? 0);
    $isCreate = ($id <= 0);

    $this->app->permissions->check($this->model->fullName . ($isCreate ? ':Create' : ':Update'));

    // $this->app->pdo->beginTransaction();

    $originalRecord = $record;
    $savedRecord = $record;

    $this->validate($savedRecord);

    try {

      $columns = $this->model->getColumns();

      foreach ($savedRecord as $key => $value) {
        if (!isset($columns[$key])) {
          unset($savedRecord[$key]);
        } else if ($value['_useMasterRecordId_'] ?? false) {
          $savedRecord[$key] = $idMasterRecord;
        }
      }

      if ((bool) ($record['_toBeDeleted_'] ?? false)) {
        $this->delete((int) $savedRecord['id']);
        $savedRecord = [];
      } else if ($isCreate) {
        $savedRecord = $this->model->onBeforeCreate($savedRecord);
        $savedRecord = $this->create($savedRecord);
        $savedRecord = $this->model->onAfterCreate($originalRecord, $savedRecord);
      } else {
        $savedRecord = $this->model->onBeforeUpdate($savedRecord);
        $savedRecord = $this->update($savedRecord);
        $savedRecord = $this->model->onAfterUpdate($originalRecord, $savedRecord);
      }

      foreach ($this->model->relations as $relName => $relDefinition) {
        if (isset($record[$relName]) && is_array($record[$relName])) {
          list($relType, $relModelClass) = $relDefinition;
          $relModel = new $relModelClass($this->app);
          switch ($relType) {
            case \ADIOS\Core\Model::HAS_MANY:
              foreach ($record[$relName] as $subKey => $subRecord) {
                $subRecord = $relModel->recordManager->save($subRecord, $savedRecord['id']);
                $savedRecord[$relName][$subKey] = $subRecord;
              }
            break;
            case \ADIOS\Core\Model::HAS_ONE:
              $subRecord = $relModel->recordManager->save($record[$relName], $savedRecord['id']);
              $savedRecord[$relName] = $subRecord;
            break;
          }
        }
      }
    } catch (\Exception $e) {
      $exceptionClass = get_class($e);

      switch ($exceptionClass) {
        case 'Illuminate\\Database\\QueryException':
          throw new $exceptionClass($e->getConnectionName(), $e->getSql(), $e->getBindings(), $e);
        break;
        case 'Illuminate\\Database\\UniqueConstraintViolationException';
          if ($e->errorInfo[1] == 1062) {
            $columns = $this->model->getColumns();

            preg_match("/Duplicate entry '(.*?)' for key '(.*?)'/", $e->errorInfo[2], $m);
            $invalidIndex = $m[2];
            $invalidValue = $m[1];
            $invalidIndexName = $columns[$invalidIndex]->getTitle();

            $errorMessage = "Value '{$invalidValue}' for {$invalidIndexName} already exists.";

            throw new \ADIOS\Core\Exceptions\RecordSaveException(
              $errorMessage,
              $e->errorInfo[1]
            );
          } else {
            throw new \ADIOS\Core\Exceptions\RecordSaveException(
              $e->errorInfo[2],
              $e->errorInfo[1]
            );
          }
        break;
        default:
          throw new $exceptionClass($e->getMessage(), $e->getCode(), $e);
        break;
      }
    }

    return $savedRecord;
  }


  /**
   * validate
   * @param array<string, mixed> $record
   * @return array<string, mixed>
   */
  public function validate(array $record): array
  {
    $invalidInputs = [];

    foreach ($this->model->getColumns() as $colName => $column) {
      if (
        $column->getRequired()
        && (!isset($record[$colName]) || $record[$colName] === null || $record[$colName] === '')
      ) {
        $invalidInputs[] = $this->app->translate(
          "`{{ colTitle }}` is required.",
          ['colTitle' => $column->getTitle()]
        );
      } else if (isset($record[$colName]) && !$column->validate($record[$colName])) {
        $invalidInputs[] = $this->app->translate(
          "`{{ colTitle }}` contains invalid value.",
          ['colTitle' => $column->getTitle()]
        );
      }
    }

    if (!empty($invalidInputs)) {
      throw new \ADIOS\Core\Exceptions\RecordSaveException(json_encode($invalidInputs), 87335);
    }

    return $record;
  }

  public function normalize(array $record): array {
    $columns = $this->model->getColumns();

    foreach ($record as $colName => $colValue) {
      if (!isset($columns[$colName])) {
        unset($record[$colName]);
      } else {
        $record[$colName] = $columns[$colName]->normalize($record[$colName]);
        if ($record[$colName] === null) unset($record[$colName]);
      }
    }

    foreach ($columns as $colName => $column) {
      if (!isset($record[$colName])) $record[$colName] = $column->getNullValue();
    }

    return $record;
  }

}