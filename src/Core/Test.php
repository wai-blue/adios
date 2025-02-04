<?php

namespace ADIOS\Core;

class Test {
  public \ADIOS\Core\Loader $app;

  public function __construct(\ADIOS\Core\Loader $app)
  {
    $this->app = $app;
  }

}