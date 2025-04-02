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

  public function getDateShortFormat(): string
  {
    return $this->locale["date"]["formatShort"] ?? "d.m.Y";
  }

  public function getDateLongFormat(): string
  {
    return $this->locale["date"]["formatLong"] ?? "d.m.Y";
  }

  public function getDatetimeFormat(): string
  {
    return $this->locale["datetime"]["format"] ?? "d.m.Y H:i:s";
  }

  public function getTimeFormat(): string
  {
    return $this->locale["time"]["format"] ?? "H:i:s";
  }

  public function getCurrencySymbol(): string
  {
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

  public function formatDateShort(string|int $dateOrTimestamp): string
  {
    if (is_string($dateOrTimestamp)) $ts = strtotime($dateOrTimestamp);
    else $ts = $dateOrTimestamp;
    return date($this->getDateShortFormat(), $ts);
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

  public function formatTime(string|int $timeOrTimestamp): string
  {
    if (is_string($dateOrTimestamp)) $ts = strtotime($timeOrTimestamp);
    else $ts = $timeOrTimestamp;
    return date($this->getTimeFormat(), $ts);
  }

}