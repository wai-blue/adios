<?php

namespace ADIOS\Controllers\Api\Record;

class Lookup extends \ADIOS\Core\ApiController {
  public bool $hideDefaultDesktop = true;

  public \ADIOS\Core\Model $model;

  function __construct(\ADIOS\Core\Loader $app, array $params = []) {
    parent::__construct($app, $params);

    $model = $this->app->urlParamAsString('model');
    // $this->permission = $model . ':Read';
    $this->model = $this->app->getModel($model);
  }

  public function response(): array
  {
    $search = $this->app->urlParamAsString('search');
    $query = $this->model->record->prepareLookupQuery($search);

    $dataRaw = $query->get()->toArray();
    $data = [];

    if (is_array($dataRaw)) {
      $data = $this->model->record->prepareLookupData($dataRaw);
    }

    return \ADIOS\Core\Helper::keyBy('id', $data);
  }

}
