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
      foreach ($dataRaw as $key => $value) {
        $data[$key]['_LOOKUP'] = $value['_LOOKUP'];
        if (!empty($value['_LOOKUP_CLASS'])) $data[$key]['_LOOKUP_CLASS'] = $value['_LOOKUP_CLASS'];
        if (isset($value['id'])) {
          $data[$key]['id'] = \ADIOS\Core\Helper::encrypt($value['id']);
        }
        if (!empty($this->model->lookupUrlDetail)) {
          $data[$key]['_URL_DETAIL'] = str_replace('{%ID%}', $value['id'], $this->model->lookupUrlDetail);
        }
        if (!empty($this->model->lookupUrlAdd)) {
          $data[$key]['_URL_ADD'] = $this->this->model->lookupUrlAdd;
        }
      }
    }

    return \ADIOS\Core\Helper::keyBy('id', $data);
  }

}
