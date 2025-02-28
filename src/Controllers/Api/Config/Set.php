<?php

namespace ADIOS\Controllers\Api\Config;

class Set extends \ADIOS\Core\ApiController {
  public function response(): array
  {
    $path = $this->app->urlParamAsString('path');
    $value = $this->app->urlParamAsString('value');

    $this->app->config->set($path, $value);

    return [
      'status' => true
    ];
  }

}
