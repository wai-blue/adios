<?php

namespace ADIOS\Controllers\Api\Record;

class Get extends \ADIOS\Core\ApiController {

  public \ADIOS\Core\Model $model;

  function __construct(\ADIOS\Core\Loader $app, array $params = [])
  {
    parent::__construct($app, $params);

    $model = $this->app->urlParamAsString('model');
    $this->permission = $model . ':Read';
    $this->model = $this->app->getModel($model);
  }

  public function response(): array
  {
    $record = [];

    $idEncrypted = $this->app->urlParamAsString('id');
    $id = (int) \ADIOS\Core\Helper::decrypt($idEncrypted);

    if ($id > 0) {
      $record = $this->model->recordGet(
        function($q) use ($id) { $q->where($this->model->table . '.id', $id); }
      );
    }

    return $record;
  }

}
