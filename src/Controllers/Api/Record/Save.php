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

  public function response(): array
  {
    $originalRecord = $this->app->urlParamAsArray('record');
    $modelClass = $this->app->urlParamAsString('model');

    if (empty($modelClass)) throw new \Exception("Master model is not specified.");

    $model = $this->app->getModel($modelClass);
    if (!is_object($model)) throw new \Exception("Unable to create model {$model}.");

    $savedRecord = $this->model->recordManager->save($originalRecord);

    return [
      'status' => 'success',
      'originalRecord' => $originalRecord,
      'savedRecord' => $savedRecord,
    ];
  }

}
