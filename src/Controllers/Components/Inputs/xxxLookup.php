<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Controllers\Components\Inputs;

/**
 * @package Components\Controllers\Lookup
 */
class Lookup extends \ADIOS\Core\Controller {
  public bool $hideDefaultDesktop = true;

  public \ADIOS\Core\Model $model;

  function __construct(\ADIOS\Core\Loader $app, array $params = []) {
    parent::__construct($app, $params);
    $this->permission = $this->app->params['model'] . ':Read';
    $this->model = $this->app->getModel($this->app->params['model']);
  }

  public function prepareLoadRecordQuery(): \Illuminate\Database\Eloquent\Builder {

    $lookupSqlValue = "(" .
      str_replace("{%TABLE%}.", '', $this->model->lookupSqlValue())
      . ") as text";

    $query = $this->model->prepareLoadRecordQuery()->selectRaw('id, ' . $lookupSqlValue);

    if ($this->app->params['search']) {
      $query->where(function($q) {
        foreach ($this->model->columns() as $columnName => $column) {
          $q->orWhere($columnName, 'LIKE', '%' . $this->app->params['search'] . '%');
        }
      });
    }

    return $query;
  }

  public function postprocessData(array $data): array {
    if (is_array($data['data'])) {
      foreach ($data['data'] as $key => $value) {
        if (isset($value['id'])) {
          $data['data'][$key]['id'] = base64_encode(openssl_encrypt($value['id'], 'AES-256-CBC', _ADIOS_ID, 0, _ADIOS_ID));
        }
      }
    }
    return $data;
  }

  public function loadData(): array {
    $data = $this->prepareLoadRecordQuery()->get()->toArray();
    $data = $this->postprocessData($data);
    return $data;
  }

  public function renderJson(): ?array { 
    try {
      $data = $this->loadData();
      return [
        'data' => \ADIOS\Core\Helper::keyBy('id', $data)
      ];
    } catch (QueryException $e) {
      http_response_code(500);

      return [
        'status' => 'error',
        'message' => $e->getMessage() 
      ];
    } catch (\Exception $e) {
      http_response_code(400);

      return [
        'status' => 'error',
        'message' => $e->getMessage() 
      ];
    }
  }

}
