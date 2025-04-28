<?php

namespace ADIOS\Controllers\Api\Record;

use Illuminate\Support\Str;

class GetList extends \ADIOS\Core\ApiController {
  public \ADIOS\Core\Model $model;
  // public array $data = [];
  // private int $itemsPerPage = 15;

  function __construct(\ADIOS\Core\Loader $app, array $params = []) {
    parent::__construct($app, $params);

    $model = $this->app->urlParamAsString('model');
    $this->permission = $model . ':Read';
    $this->model = $this->app->getModel($model);
  }

  public function response(): array
  {
    return $this->model->recordGetList(
      $this->app->urlParamAsString('fulltextSearch'),
      $this->app->urlParamAsArray('columnSearch'),
      $this->app->urlParamAsArray('orderBy'),
      $this->app->urlParamAsInteger('itemsPerPage', 15),
      $this->app->urlParamAsInteger('page'),
    );
  }
}
