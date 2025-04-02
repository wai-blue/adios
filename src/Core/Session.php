<?php

namespace ADIOS\Core;

class Session
{
  public \ADIOS\Core\Loader $app;

  private string $salt = '';

  public function __construct(\ADIOS\Core\Loader $app)
  {
    $this->app = $app;

    if (isset($_SESSION) && is_array($_SESSION) && !is_array($_SESSION[$this->salt])) $_SESSION[$this->salt] = [];

    $this->salt = $this->app->config->getAsString('sessionSalt');
  }

  public function getSalt(): string
  {
    return $this->salt;
  }

  public function start(): void
  {
    session_id();
    session_name($this->salt);
    session_start();

    define('_SESSION_ID', session_id());
  }

  public function set(string $path, mixed $value, string $key = '')
  {
    if (empty($key)) $key = $this->salt;
    if (!isset($_SESSION[$key])) $_SESSION[$key] = [];
    $_SESSION[$key][$path] = $value;
  }

  public function get(string $path = '', string $key = ''): mixed
  {
    if (empty($key)) $key = $this->salt;
    if ($path == '') return $_SESSION[$key] ?? [];
    else return $_SESSION[$key][$path] ?? null;
  }

  public function push(string $path, mixed $value): void
  {
    if (!is_array($_SESSION[$this->salt][$path])) $_SESSION[$this->salt][$path] = [];
    $_SESSION[$this->salt][$path][] = $value;
  }

  public function isset(string $path): bool
  {
    return isset($_SESSION[$this->salt][$path]);
  }

  public function unset(string $path): void
  {
    if ($this->isset($path)) unset($_SESSION[$this->salt][$path]);
  }

  public function clear(): void
  {
    unset($_SESSION[$this->salt]);
  }

}