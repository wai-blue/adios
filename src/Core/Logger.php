<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/


namespace ADIOS\Core;

use Monolog\Handler\RotatingFileHandler;

/**
 * Debugger console for ADIOS application.
 */
class Logger {
  public \ADIOS\Core\Loader $app;

  public array $loggers = [];

  public bool $cliEchoEnabled = false;
  public string $logFolder = "";
  public bool $enabled = false;
 
  public function __construct($app) {
    $this->app = $app;
    $this->logFolder = $this->app->config->getAsString('logFolder');
    $this->enabled = !empty($this->logFolder) && is_dir($this->logFolder);

    $this->initLogger('core');
  }

  public function initLogger(string $loggerName = "") {
    if (!class_exists("\\Monolog\\Logger")) return;

    // inicializacia loggerov
    $this->loggers[$loggerName] = new \Monolog\Logger($loggerName);
    $infoStreamHandler = new RotatingFileHandler("{$this->logFolder}/{$loggerName}-info.log", 1000, \Monolog\Logger::INFO);
    $infoStreamHandler->setFilenameFormat('{date}/{filename}', 'Y/m/d');

    $warningStreamHandler = new RotatingFileHandler("{$this->logFolder}/{$loggerName}-warning.log", 1000, \Monolog\Logger::WARNING);
    $warningStreamHandler->setFilenameFormat('{date}/{filename}', 'Y/m/d');

    $errorStreamHandler = new RotatingFileHandler("{$this->logFolder}/{$loggerName}-error.log", 1000, \Monolog\Logger::ERROR);
    $errorStreamHandler->setFilenameFormat('{date}/{filename}', 'Y/m/d');

    $this->loggers[$loggerName]->pushHandler($infoStreamHandler);
    $this->loggers[$loggerName]->pushHandler($warningStreamHandler);
    $this->loggers[$loggerName]->pushHandler($errorStreamHandler);

  }
  
  public function getLogger($loggerName) {
    if (!isset($this->loggers[$loggerName])) {
      $this->initLogger($loggerName);
    }

    return $this->loggers[$loggerName];
  }

  public function cliEcho($message, $loggerName, $severity) {
    if ($this->cliEchoEnabled && php_sapi_name() === 'cli') {
      echo date("Y-m-d H:i:s")." {$loggerName}.{$severity} {$message}\n";
    }
  }

  public function info($message, array $context = [], $loggerName = 'core') {
    if (!$this->enabled) return;
    $this->getLogger($loggerName)->info($message, $context);
    $this->cliEcho($message, $loggerName, 'INFO');
  }
  
  public function warning($message, array $context = [], $loggerName = 'core') {
    if (!$this->enabled) return;
    $this->getLogger($loggerName)->warning($message, $context);
    $this->cliEcho($message, $loggerName, 'WARNING');
  }
  
  public function error($message, array $context = [], $loggerName = 'core') {
    if (!$this->enabled) return;
    $this->getLogger($loggerName)->error($message, $context);
    $this->cliEcho($message, $loggerName, 'ERROR');
  }

}