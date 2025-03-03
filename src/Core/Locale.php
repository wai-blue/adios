<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core;

class Locale {
  public ?\ADIOS\Core\Loader $app = null;

  private array $locale = [];
  
  public function __construct($app) {
    $this->app = $app;
    $this->locale = $this->app->config->getAsArray('locale');
  }

  public function dateFormat() {
    return $this->locale["date"]["format"] ?? "d.m.Y";
  }

  public function datetimeFormat() {
    return $this->locale["datetime"]["format"] ?? "d.m.Y H:i:s";
  }

  public function timeFormat() {
    return $this->locale["time"]["format"] ?? "H:i:s";
  }

  public function currencySymbol() {
    return $this->locale["currency"]["symbol"] ?? "€";
  }

  public function getAll(string $keyBy = "") {
    return [
      "dateFormat" => $this->dateFormat(),
      "timeFormat" => $this->timeFormat(),
      "datetimeFormat" => $this->datetimeFormat(),
      "currencySymbol" => $this->currencySymbol(),
    ];
  }

}