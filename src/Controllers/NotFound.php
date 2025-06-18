<?php

namespace ADIOS\Controllers;

class NotFound extends \ADIOS\Core\Controller {
  public bool $requiresUserAuthentication = false;
  public bool $hideDefaultDesktop = true;

  public function prepareView(): void
  {
    $this->setView('@app/Views/404.twig');
  }
}