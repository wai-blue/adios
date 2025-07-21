<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core;

class Locale {
  public \ADIOS\Core\Loader $app;

  private array $locale = [];
  
  public function __construct($app) {
    $this->app = $app;
    $this->locale = $this->app->config->getAsArray('locale');
  }

  public function getDateShortFormat(): string
  {
    return "Y-m-d";
  }

  public function getDateLongFormat(): string
  {
    return "Y-m-d";
  }

  public function getDatetimeFormat(): string
  {
    return "Y-m-d H:i:s";
  }

  public function getTimeFormat(bool $addSeconds = true): string
  {
    return "H:i" . ($addSeconds ? ":s" : "");
  }

  public function getCurrencySymbol(): string
  {
    return "€";
  }

  public function getAll(string $keyBy = "") {
    return [
      "dateFormat" => $this->getDateFormat(),
      "timeFormat" => $this->getTimeFormat(),
      "datetimeFormat" => $this->getDatetimeFormat(),
      "currencySymbol" => $this->getCurrencySymbol(),
    ];
  }

  public function formatCurrency(string|float $value, string $symbol = ''): string
  {
    if ($symbol == '') $symbol = $this->getCurrencySymbol();
    return number_format((float) $value, 2, ",", " ") . ' ' . $symbol;
  }

  public function formatDateShort(string|int $dateOrTimestamp): string
  {
    if (is_string($dateOrTimestamp)) $ts = strtotime($dateOrTimestamp);
    else $ts = $dateOrTimestamp;
    return $ts . '-' . date($this->getDateShortFormat(), $ts);
  }

  public function formatDateLong(string|int $dateOrTimestamp): string
  {
    if (is_string($dateOrTimestamp)) $ts = strtotime($dateOrTimestamp);
    else $ts = $dateOrTimestamp;
    return date($this->getDateLongFormat(), $ts);
  }

  public function formatDatetime(string|int $datetimeOrTimestamp): string
  {
    if (is_string($dateOrTimestamp)) $ts = strtotime($datetimeOrTimestamp);
    else $ts = $datetimeOrTimestamp;
    return date($this->getDatetimeFormat(), $ts);
  }

  public function formatTime(string|int $timeOrTimestamp, bool $addSeconds = true): string
  {
    if (is_string($timeOrTimestamp)) $ts = strtotime($timeOrTimestamp);
    else $ts = $timeOrTimestamp;
    return date($this->getTimeFormat($addSeconds), $ts);
  }

}