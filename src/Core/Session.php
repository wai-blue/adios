<?php

namespace ADIOS\Core;

class Session
{
  public \ADIOS\Core\Loader $app;

  public function __construct(\ADIOS\Core\Loader $app)
  {
    $this->app = $app;

    if (isset($_SESSION) && is_array($_SESSION) && !is_array($_SESSION[_ADIOS_ID])) $_SESSION[_ADIOS_ID] = [];
  }

  public function set(string $path, mixed $value)
  {
    if (!isset($_SESSION[_ADIOS_ID])) $_SESSION[_ADIOS_ID] = [];
    $_SESSION[_ADIOS_ID][$path] = $value;
  }

  public function get(string $path = ''): mixed
  {
    if ($path == '') return $_SESSION[_ADIOS_ID] ?? [];
    else return $_SESSION[_ADIOS_ID][$path] ?? null;
  }

  public function push(string $path, mixed $value): void
  {
    if (!is_array($_SESSION[_ADIOS_ID][$path])) $_SESSION[_ADIOS_ID][$path] = [];
    $_SESSION[_ADIOS_ID][$path][] = $value;
  }

  public function isset(string $path): bool
  {
    return isset($_SESSION[_ADIOS_ID][$path]);
  }

  public function unset(string $path): void
  {
    if ($this->isset($path)) unset($_SESSION[_ADIOS_ID][$path]);
  }

  public function clear(): void
  {
    unset($_SESSION[_ADIOS_ID]);
  }

}