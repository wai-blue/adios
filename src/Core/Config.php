<?php

namespace ADIOS\Core;

class Config
{
  public \ADIOS\Core\Loader $app;

  protected array $config = [];

  public function __construct(\ADIOS\Core\Loader $app, array $config)
  {
    $this->app = $app;

    $this->config = $config;

    if (empty($this->config['dir'])) $this->config['dir'] = "";
    if (empty($this->config['url'])) $this->config['url'] = "";
    if (empty($this->config['rewriteBase'])) $this->config['rewriteBase'] = "";
    if (empty($this->config['appDir'])) $this->config['appDir'] = $this->config['dir'];
    if (empty($this->config['appUrl'])) $this->config['appUrl'] = $this->config['url'];
    if (empty($this->config['accountDir'])) $this->config['accountDir'] = $this->config['dir'];
    if (empty($this->config['accountUrl'])) $this->config['accountUrl'] = $this->config['url'];
    if (empty($this->config['sessionSalt'])) $this->config['sessionSalt'] = $this->config['appNamespace'] ?? 'adios-session';

    $this->set('requestUri', $_SERVER['REQUEST_URI'] ?? "");

  }

  public function empty(string $path): bool
  {
    if (!isset($this->config[$path])) return false;
    else return empty($this->config[$path]);
  }

  public function get(string $path = '', $default = null): mixed
  {
    if ($path === '') return $this->config;
    else {
      $config = $this->config;
      foreach (explode('/', $path) as $key => $value) {
        if (isset($config[$value])) {
          $config = $config[$value];
        } else {
          $config = null;
        }
      }
      return ($config === null ? $default : $config);
    }
  }

  public function getAsString(string $path, string $defaultValue = ''): string
  {
    return (string) $this->get($path, $defaultValue);
  }

  public function getAsInteger(string $path, int $defaultValue = 0): int
  {
    return (int) $this->get($path, $defaultValue);
  }

  public function getAsFloat(string $path, float $defaultValue = 0): float
  {
    return (float) $this->get($path, $defaultValue);
  }

  public function getAsBool(string $path, bool $defaultValue = false): bool
  {
    return (bool) $this->get($path, $defaultValue);
  }

  public function getAsArray(string $path, array $defaultValue = []): array
  {
    return (array) $this->get($path, $defaultValue);
  }





  public function set(string $path, mixed $value): void
  {
    $path_array = explode('/', $path);

    $cfg = &$this->config;
    foreach ($path_array as $path_level => $path_slice) {
      if ($path_level == count($path_array) - 1) {
        $cfg[$path_slice] = $value;
      } else {
        if (empty($cfg[$path_slice])) {
          $cfg[$path_slice] = null;
        }
        $cfg = &$cfg[$path_slice];
      }
    }
  }

  public function save(string $path, string $value): void
  {
    try {
      if (!empty($path)) {
        $this->app->pdo->execute("
          insert into `config` set `path` = :path, `value` = :value
          on duplicate key update `path` = :path, `value` = :value
        ", ['path' => $path, 'value' => $value]);
      }
    } catch (\Exception $e) {
    }
  }

  public function saveForUser(string $path, string $value): void
  {
    $this->save('user/' . $this->app->auth->getUserId() . '/' . $path, $value);
  }

  public function delete($path): void
  {
    try {
      if (!empty($path)) {
        $this->app->pdo->execute("delete from `config` where `path` like ?", [$path . '%']);
      }
    } catch (\Exception $e) {
      if ($e->getCode() == '42S02') { // Base table not found
        // do nothing
      } else {
        throw $e; // forward exception to be processed by ADIOS
      }
    }
  }

  public function loadFromDB(): void
  {
    if (!$this->app->pdo->isConnected) return;

    try {
      $cfgs = $this->app->pdo->fetchAll("select * from `config`");

      foreach ($cfgs as $cfg) {
        $tmp = &$this->config;
        foreach (explode("/", $cfg['path']) as $tmp_path) {
          if (!isset($tmp[$tmp_path])) {
            $tmp[$tmp_path] = [];
          }
          $tmp = &$tmp[$tmp_path];
        }
        $tmp = $cfg['value'];
      }
    } catch (\Exception $e) {
      if ($e->getCode() == '42S02') { // Base table not found
        // do nothing
      } else {
        throw $e; // forward exception to be processed by ADIOS
      }
    }
  }

  public function filterByUser(): void
  {
    $idUser = $this->app->auth->getUserId();
    if (isset($this->config['user'][$idUser]) && is_array($this->config['user'][$idUser])) {
      $this->config = array_merge_recursive($this->config, $this->config['user'][$idUser]);
      unset($this->config['user']);
    }
  }

  // public function finalize() {
  //   // various default values
  //   $this->config['protocol'] = (strtoupper($_SERVER['HTTPS'] ?? "") == "ON" ? "https" : "http");
  //   $this->config['timezone'] = $this->config['timezone'] ?? 'Europe/Bratislava';

  //   $this->config['uploadDir'] = $this->config['uploadDir'] ?? "{$this->config['accountDir']}/upload";
  //   $this->config['uploadUrl'] = $this->config['uploadUrl'] ?? "{$this->config['accountUrl']}/upload";

  //   $this->config['uploadDir'] = str_replace("\\", "/", $this->config['uploadDir']);
  // }

}