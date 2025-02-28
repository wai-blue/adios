<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/


namespace ADIOS\Core;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

/**
 * Debugger console for ADIOS application.
 */
class Console {
  public ?\ADIOS\Core\Loader $app = null;

  public array $loggers = [];
  public array $infos = [];
  public array $warnings = [];
  public array $errors = [];
  
  public bool $cliEchoEnabled = FALSE;

  public int $lastTimestamp = 0;

  public string $logDir = "";
 
  public function __construct($app) {
    $this->app = $app;
    $this->logDir = $this->app->config->getAsString('logDir');

    $this->initLogger('core');
  }

  public function initLogger(string $loggerName = "") {
    if (!class_exists("\\Monolog\\Logger")) return;

    // inicializacia loggerov
    $this->loggers[$loggerName] = new Logger($loggerName);
    $infoStreamHandler = new RotatingFileHandler("{$this->logDir}/{$loggerName}-info.log", 1000, Logger::INFO);
    $infoStreamHandler->setFilenameFormat('{date}/{filename}', 'Y/m/d');

    $warningStreamHandler = new RotatingFileHandler("{$this->logDir}/{$loggerName}-warning.log", 1000, Logger::WARNING);
    $warningStreamHandler->setFilenameFormat('{date}/{filename}', 'Y/m/d');

    $errorStreamHandler = new RotatingFileHandler("{$this->logDir}/{$loggerName}-error.log", 1000, Logger::ERROR);
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
    $this->getLogger($loggerName)->info($message, $context);
    $this->infos[microtime()] = [$message, $context];
  
    $this->cliEcho($message, $loggerName, 'INFO');
  }
  
  public function warning($message, array $context = [], $loggerName = 'core') {
    $this->getLogger($loggerName)->warning($message, $context);
    $this->warnings[microtime()] = [$message, $context];

    $this->cliEcho($message, $loggerName, 'WARNING');
  }
  
  public function error($message, array $context = [], $loggerName = 'core') {
    $this->getLogger($loggerName)->error($message, $context);
    $this->errors[microtime()] = [$message, $context];

    $this->cliEcho($message, $loggerName, 'ERROR');
  }

}