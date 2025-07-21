<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core;

class DependencyInjection {
  public \ADIOS\Core\Loader $app;

  /**
   * [Description for $dependencies]
   *
   * @var array<string, string>
   */
  private array $dependencies = [];
  
  public function __construct($app) {
    $this->app = $app;

    $this->setDependency('model.user', \ADIOS\Models\User::class);
  }

  public function setDependency(string $service, string $class): void
  {
    $this->dependencies[$service] = $class;
  }

  public function create(string $service): mixed
  {
    $class = $this->dependencies[$service] ?? $service;
    return (new $class($this->app));
  }

}