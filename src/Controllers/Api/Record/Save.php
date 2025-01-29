<?php

namespace ADIOS\Controllers\Api\Record;

class Save extends \ADIOS\Core\ApiController {
  protected ?\Illuminate\Database\Eloquent\Builder $query = null;

  public \ADIOS\Core\Model $model;

  function __construct(\ADIOS\Core\Loader $app, array $params = []) {
    parent::__construct($app, $params);
    $model = $this->app->urlParamAsString('model');
    $this->permission = $model . ':Create';
    $this->model = $this->app->getModel($model);
  }

  public function recordSave(
    string $modelClass,
    array $data,
    int $idMasterRecord = 0
  ): array {
    $savedRecord = [];

    if (empty($modelClass)) throw new \Exception("Master model is not specified.");
    $model = $this->app->getModel($modelClass);
    if (!is_object($model)) throw new \Exception("Unable to create model {$model}.");

    if ($idMasterRecord == 0) $pdo = $model->eloquent->getConnection()->getPdo();
    else $pdo = null;

    if ($pdo) $pdo->beginTransaction();

    $dataToSave = $data;

    try {

      foreach ($dataToSave as $key => $value) {
        if ($value['_useMasterRecordId_'] ?? false) {
          $dataToSave[$key] = $idMasterRecord;
        }
      }

      if ((bool) ($dataToSave['_toBeDeleted_'] ?? false)) {
        $model->recordDelete((int) $dataToSave['id']);
        $savedRecord = [];
      } else {
        $masterRecordSaved = $model->recordSave($dataToSave);
        $idMasterRecord = (int) $masterRecordSaved['id'];

        if ($idMasterRecord > 0) {
          $savedRecord = $model->recordGet(
            function($q) use ($model, $idMasterRecord) { $q->where($model->table . '.id', $idMasterRecord); },
            $this->app->urlParamAsArray('includeRelations'),
            $this->app->urlParamAsInteger('maxRelationLevel', 1)
          );
        }
      }

      foreach ($model->relations as $relName => $relDefinition) {
        if (isset($data[$relName]) && is_array($data[$relName])) {
          list($relType, $relModel) = $relDefinition;
          switch ($relType) {
            case \ADIOS\Core\Model::HAS_MANY:
              foreach ($data[$relName] as $subKey => $subRecord) {
                $subRecord = $this->recordSave($relModel, $subRecord, $idMasterRecord);
                $savedRecord[$relName][$subKey] = (int) $subRecord['id'];
              }
            break;
            case \ADIOS\Core\Model::HAS_ONE:
              $subRecord = $this->recordSave($relModel, $data[$relName], $idMasterRecord);
              $savedRecord[$relName] = (int) $subRecord['id'];
            break;
          }
        }
      }

      if ($pdo) $pdo->commit();
    } catch (\Exception $e) {
      $exceptionClass = get_class($e);
      if ($pdo) $pdo->rollBack();

      switch ($exceptionClass) {
        case 'Illuminate\\Database\\QueryException':
          throw new $exceptionClass($e->getConnectionName(), $e->getSql(), $e->getBindings(), $e);
        break;
        case 'Illuminate\\Database\\UniqueConstraintViolationException';
          if ($e->errorInfo[1] == 1062) {
            $columns = $this->model->columns();

            preg_match("/Duplicate entry '(.*?)' for key '(.*?)'/", $e->errorInfo[2], $m);
            $invalidIndex = $m[2];
            $invalidValue = $m[1];
            $invalidIndexName = $columns[$invalidIndex]["title"];

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

  public function response(): array
  {
    $originalRecord = $this->app->urlParamAsArray('record');
    $model = $this->app->urlParamAsString('model');

    $decryptedRecord = $this->model->recordDecryptIds($originalRecord);

    $savedRecord = $this->recordSave($model, $decryptedRecord);

    return [
      'status' => 'success',
      'originalRecord' => $originalRecord,
      'savedRecord' => $savedRecord,
    ];
  }

}
