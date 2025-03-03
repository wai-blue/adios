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

    $this->salt = $this->app->config->getAsString('sessionSalt') . "-" . substr(md5($this->app->config->getAsString('sessionSalt')), 0, 5);
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

  public function set(string $path, mixed $value)
  {
    if (!isset($_SESSION[$this->salt])) $_SESSION[$this->salt] = [];
    $_SESSION[$this->salt][$path] = $value;
  }

  public function get(string $path = ''): mixed
  {
    if ($path == '') return $_SESSION[$this->salt] ?? [];
    else return $_SESSION[$this->salt][$path] ?? null;
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