<?php

namespace ADIOS\Controllers\Api\Record;

class Lookup extends \ADIOS\Core\ApiController {
  public bool $hideDefaultDesktop = true;

  public \ADIOS\Core\Model $model;

  function __construct(\ADIOS\Core\Loader $app, array $params = []) {
    parent::__construct($app, $params);

    $model = $this->app->urlParamAsString('model');
    $this->permission = $model . ':Read';
    $this->model = $this->app->getModel($model);
  }

  public function prepareLoadRecordQuery(): \Illuminate\Database\Eloquent\Builder {

    $query = $this->model->prepareLoadRecordQuery();

    $search = $this->app->urlParamAsString('search');
    if (!empty($search)) {
      $query->where(function($q) {
        foreach ($this->model->columns() as $columnName => $column) {
          $q->orWhere($this->model->table . '.' . $columnName, 'LIKE', '%' . $search . '%');
        }
      });
    }

    return $query;
  }

  public function response(): array
  {
    $data = $this->prepareLoadRecordQuery()->get()->toArray();

    if (is_array($data)) {
      foreach ($data as $key => $value) {
        if (isset($value['id'])) {
          $data[$key]['id'] = \ADIOS\Core\Helper::encrypt($value['id']);
        }
      }
    }

    return \ADIOS\Core\Helper::keyBy('id', $data);
  }

}
