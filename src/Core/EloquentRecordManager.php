<?php

namespace ADIOS\Core;

class EloquentRecordManager extends \Illuminate\Database\Eloquent\Model implements RecordManagerInterface {
  protected $primaryKey = 'id';
  protected $guarded = [];
  public $timestamps = false;
  public static $snakeAttributes = false;
  

  public ?\ADIOS\Core\Loader $app;
  public ?\ADIOS\Core\Model $model;

  // /** What relations to be included in loaded record. If null, default relations will be selected. */
  // /** @property array<string> */
  // protected array $relationsToRead = [];

  protected int $maxReadLevel = 2;

  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
    $this->app = null;
    $this->model = null;
  }

  // public function getRelationsToRead(): array
  // {
  //   return $this->relationsToRead;
  // }

  // public function setRelationsToRead(array $relationsToRead): void
  // {
  //   $this->relationsToRead = $relationsToRead;
  // }

  public function getMaxReadLevel(): array
  {
    return $this->maxReadLevel;
  }

  public function setMaxReadLevel(array $maxReadLevel): void
  {
    $this->maxReadLevel = $maxReadLevel;
  }

  public function getPermissions(array $record): array
  {
    $permissions = [true, true, true, true];
  }

  /**
   * prepareReadQuery
   * @param mixed $query Leave empty for default behaviour.
   * @param int $level Leave empty for default behaviour.
   * @return mixed Eloquent query used to read record.
   */
  public function prepareReadQuery(mixed $query = null, int $level = 0): mixed
  {
    if ($query === null) $query = $this;

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
        $lookupConnection = $lookupModel->record->getConnectionName();
        $lookupDatabase = $lookupModel->record->getConnection()->getDatabaseName();
        $lookupTableName = $lookupModel->getFullTableSqlName();
        $joinAlias = 'join_' . $columnName;

        // $selectRaw[] = "(" .
        //   str_replace("{%TABLE%}", $joinAlias, $lookupModel->getLookupSqlValue())
        //   . ") as `_LOOKUP[{$columnName}]`"
        // ;
        $selectRaw[] =
          "(select _LOOKUP from ("
          . $lookupModel->record->prepareLookupQuery('')->toRawSql()
          . ") dummy where `id` = `{$this->table}`.`{$columnName}`) as `_LOOKUP[{$columnName}]`"
        ;

        $joins[] = [
          $lookupDatabase . '.' . $lookupTableName . ' as ' . $joinAlias,
          $joinAlias.'.id',
          '=',
          $this->table.'.'.$columnName
        ];
      }
    }

    // TODO: Toto je pravdepodobne potencialna SQL injection diera. Opravit.
    $query = $query->selectRaw(join(',', $selectRaw)); //->with($withs);
    foreach ($this->model->relations as $relName => $relDefinition) {
      // if (count($this->relationsToRead) > 0 && !in_array($relName, $this->relationsToRead)) continue;

      $relModel = new $relDefinition[1]($this->app);

      if ($level <= $this->maxReadLevel) {
        $query->with([$relName => function($q) use($relModel, $level) {
          return $relModel->record->prepareReadQuery($q, $level + 1);
        }]);
      }
    }

    foreach ($joins as $join) {
      $query->leftJoin($join[0], $join[1], $join[2], $join[3]);
    }

    return $query;
  }

  /**
   * prepareLookupQuery
   * @param string $searc What string to lookup for
   */
  public function prepareLookupQuery(string $search): mixed
  {
    $query = $this;

    if (!empty($search)) {
      $query = $query->where(function($q) use ($search) {
        foreach ($this->model->columnNames() as $columnName) {
          $q->orWhere($this->model->table . '.' . $columnName, 'LIKE', '%' . $search . '%');
        }
      });
    }

    $selectRaw = [];
    $selectRaw[] = $this->table . '.*';
    $selectRaw[] = '(' . str_replace('{%TABLE%}', $this->table, $this->model->getLookupSqlValue()) . ') as _LOOKUP';
    $selectRaw[] = '"" as _LOOKUP_CLASS';

    $query = $query->selectRaw(join(',', $selectRaw));

    return $query;
  }

  public function prepareLookupData(array $dataRaw): array
  {
    $data = [];

    foreach ($dataRaw as $key => $value) {
      $data[$key]['_LOOKUP'] = $value['_LOOKUP'];
      if (!empty($value['_LOOKUP_CLASS'])) $data[$key]['_LOOKUP_CLASS'] = $value['_LOOKUP_CLASS'];
      if (isset($value['id'])) {
        $data[$key]['id'] = \ADIOS\Core\Helper::encrypt($value['id']);
      }
      if (!empty($this->model->lookupUrlDetail)) {
        $data[$key]['_URL_DETAIL'] = str_replace('{%ID%}', $value['id'], $this->model->lookupUrlDetail);
      }
    }

    return $data;
  }

  public function addFulltextSearchToQuery(mixed $query, string $fulltextSearch): mixed
  {
    if (!empty($fulltextSearch)) {
      foreach ($this->model->getColumns() as $columnName => $column) {
        $enumValues = $column->getEnumValues();
        if (count($enumValues) > 0) {
          $query->orHaving('_ENUM[' . $columnName . ']', 'like', "%{$fulltextSearch}%");
        } else if ($column->getType() == 'lookup') {
          $query->orHaving('_LOOKUP[' . $columnName . ']', 'like', "%{$fulltextSearch}%");
        } else {
          $query->orHaving($columnName, 'like', "%{$fulltextSearch}%");
        }
      }
    }

    return $query;
  }

  public function addColumnSearchToQuery(mixed $query, array $columnSearch): mixed
  {
    if (count($columnSearch) > 0) {
      foreach ($this->model->getColumns() as $columnName => $column) {
        if (!empty($columnSearch[$columnName])) {
          $enumValues = $column->getEnumValues();
          if (count($enumValues) > 0) {
            $query->having('_ENUM[' . $columnName . ']', 'like', "%{$columnSearch[$columnName]}%");
          } else if ($column->getType() == 'lookup') {
            $query->having('_LOOKUP[' . $columnName . ']', 'like', "%{$columnSearch[$columnName]}%");
          } else if (in_array($column->getType(), ['int', 'decimal', 'float'])) {
            $q = trim(str_replace(' ', '', str_replace(',', '.', $columnSearch[$columnName])));

            preg_match('/(.*?)([\\d\\.]+)/', $q, $m);

            $operation = $m[1];
            $value = (float) $m[2];

            $query->where($columnName, $operation, $value);
          } else {
            $query->having($columnName, 'like', "%{$columnSearch[$columnName]}%");
          }
        }
      }
    }

    return $query;
  }

  public function addOrderByToQuery(mixed $query, array $orderBy): mixed
  {
    if (isset($orderBy['field']) && isset($orderBy['direction'])) {
      $query->orderBy($orderBy['field'], $orderBy['direction']);
    }

    return $query;
  }

  public function recordReadMany(mixed $query, int $itemsPerPage, int $page): array
  {
    $data = $query->paginate(
      $itemsPerPage,
      ['*'],
      'page',
      $page
    )->toArray();

    foreach ($data['data'] as $key => $record) {
      $permissions = $this->getPermissions($record);
      if (!$permissions[1]) {
        // cannot read
        unset($data['data'][$key]);
      } else {
        $data['data'][$key]['_PERMISSIONS'] = $permissions;
      }
    }

    // Laravel pagination
    if (!is_array($data)) $data = [];
    if (!is_array($data['data'])) $data['data'] = [];

    return $data;
  }

  public function recordRead(mixed $query): array {
    $record = $query->first()?->toArray();
    if (!is_array($record)) $record = [];

    $permissions = $this->getPermissions($record);
    if (!$permissions[1]) {
      // cannot read
      $record = [];
    };

    if ($record != []) {
      $record = $this->recordEncryptIds($record);
      $record['_PERMISSIONS'] = $permissions;
      $record['_RELATIONS'] = array_keys($this->model->relations);
    }
    // if (count($this->relationsToRead) > 0) {
    //   $record['_RELATIONS'] = array_values(array_intersect($record['_RELATIONS'], $this->relationsToRead));
    // }

    return $record;
  }

  public function recordEncryptIds(array $record): array
  {

    foreach ($this->model->getColumns() as $colName => $column) {
      $colDefinition = $column->toArray();
      if (($colName == 'id' || $colDefinition['type'] == 'lookup') && isset($record[$colName]) && $record[$colName] !== null) {
        $record[$colName] = \ADIOS\Core\Helper::encrypt($record[$colName]);
      }
    }

    $record['_idHash_'] =  \ADIOS\Core\Helper::encrypt($record['id'] ?? '', '', true);

    return $record;
  }

  public function recordDecryptIds(array $record): array
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
            $record[$relName][$subKey] = $relModel->record->recordDecryptIds($record[$relName][$subKey]);
          }
        break;
        case \ADIOS\Core\Model::HAS_ONE:
          $record[$relName] = $relModel->record->recordDecryptIds($record[$relName]);
        break;
      }
    }

    return $record;
  }

  public function recordCreate(array $record): array
  {
    unset($record['id']);
    $normalizedRecord = $this->recordNormalize($record);
    $record['id'] = $this->create($normalizedRecord)->id;
    return $record;
  }

  public function recordUpdate(array $record): array
  {
    $normalizedRecord = $this->recordNormalize($record);
    $this->find((int) ($record['id'] ?? 0))->update($normalizedRecord);
    return $record;
  }

  public function recordDelete(int|string $id): int
  {
    $record = $this->recordRead($this->where('id', $id));
    $permissions = $this->getPermissions($record);
    if (!$permissions[3]) { // cannot delete
      throw new \ADIOS\Core\Exceptions\NotEnoughPermissionsException("Cannot delete. Not enough permissions.");
    }

    $this->where('id', $id)->delete();
    return 1; // TODO: return $rowsAffected
  }

  public function recordSave(array $record, int $idMasterRecord = 0): array
  {

    $id = (int) ($record['id'] ?? 0);
    $isCreate = ($id <= 0);

    // $this->app->permissions->check($this->model->fullName . ($isCreate ? ':Create' : ':Update'));

    // $this->app->pdo->beginTransaction();

    $permissions = $this->getPermissions($record);
    if (
      ($id < 0 && !$permissions[0]) // cannot create
      || ($id >= 0 && !$permissions[2]) // cannot update
    ) {
      throw new \ADIOS\Core\Exceptions\NotEnoughPermissionsException("Cannot save. Not enough permissions.");
    }

    if ($id <= 0) $originalRecord = [];
    else $originalRecord = $this->where($this->table . '.id', $id)->first()?->toArray();

    $savedRecord = $record;
    if ($idMasterRecord == 0) $this->recordValidate($savedRecord);

    try {

      $columns = $this->model->getColumns();

      foreach ($savedRecord as $key => $value) {
        $useMasterRecordId = false;
        if (isset($value['_useMasterRecordId_'])) $useMasterRecordId = $value['_useMasterRecordId_'];
        if (isset($columns[$key]) && is_array($value) && $useMasterRecordId) {
          $savedRecord[$key] = $idMasterRecord;
        }
      }

      if ((bool) ($record['_toBeDeleted_'] ?? false)) {
        $this->model->onBeforeDelete((int) $id);
        $this->recordDelete((int) $savedRecord['id']);
        $this->model->onAfterDelete((int) $id);
        return [];
      } else if ($isCreate) {
        $savedRecord = $this->model->onBeforeCreate($savedRecord);
        $savedRecord = $this->recordCreate($savedRecord);
        $savedRecord = $this->model->onAfterCreate($originalRecord, $savedRecord);
      } else {
        $savedRecord = $this->model->onBeforeUpdate($savedRecord);
        $savedRecord = $this->recordUpdate($savedRecord);
        $savedRecord = $this->model->onAfterUpdate($originalRecord, $savedRecord);
      }

      foreach ($this->model->relations as $relName => $relDefinition) {
        if (isset($record[$relName]) && is_array($record[$relName])) {
          list($relType, $relModelClass) = $relDefinition;
          $relModel = new $relModelClass($this->app);
          switch ($relType) {
            case \ADIOS\Core\Model::HAS_MANY:
              foreach ($record[$relName] as $subKey => $subRecord) {
                if (is_array($subRecord)) {
                  $subRecord = $relModel->record->recordSave($subRecord, $savedRecord['id']);
                  $savedRecord[$relName][$subKey] = $subRecord;
                }
              }
            break;
            case \ADIOS\Core\Model::HAS_ONE:
              if (is_array($record[$relName])) {
                $subRecord = $relModel->record->recordSave($record[$relName], $savedRecord['id']);
                $savedRecord[$relName] = $subRecord;
              }
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
  public function recordValidate(array $record): array
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

    foreach ($this->model->relations as $relName => $relDefinition) {
      if (isset($record[$relName]) && is_array($record[$relName])) {
        list($relType, $relModelClass) = $relDefinition;
        $relModel = new $relModelClass($this->app);
        switch ($relType) {
          case \ADIOS\Core\Model::HAS_MANY:
            foreach ($record[$relName] as $subKey => $subRecord) {
              if (is_array($subRecord)) {
                $subRecord = $relModel->record->recordValidate($subRecord, $record['id']);
              }
            }
          break;
          case \ADIOS\Core\Model::HAS_ONE:
            if (is_array($record[$relName])) {
              $subRecord = $relModel->record->recordValidate($record[$relName], $record['id']);
            }
          break;
        }
      }
    }

    return $record;
  }

  public function recordNormalize(array $record): array {
    $columns = $this->model->getColumns();

    foreach ($record as $colName => $colValue) {
      if (!isset($columns[$colName])) {
        unset($record[$colName]);
      } else {
        $record[$colName] = $columns[$colName]->normalize($record[$colName]);
        if ($record[$colName] === null) unset($record[$colName]);
      }
    }

    // foreach ($columns as $colName => $column) {
    //   if (!isset($record[$colName])) $record[$colName] = $column->getNullValue();
    // }

    return $record;
  }

}
