<?php

namespace ADIOS\Core;

class Auth {
  public \ADIOS\Core\Loader $app;
  public array $params;
  public ?array $user = null;

  function __construct(\ADIOS\Core\Loader $app, array $params = [])
  {
    $this->app = $app;
    $this->params = $params;

    if ($this->isUserInSession()) $this->loadUserFromSession();
  }

  public function getUserFromSession(): ?array
  {
    return $this->app->session->get('userProfile');
  }

  public function updateUserInSession(array $user): void
  {
    $this->app->session->set('userProfile', $user);
  }

  public function isUserInSession(): bool
  {
    return is_array($this->getUserFromSession());
  }

  public function loadUserFromSession()
  {
    $this->user = $this->getUserFromSession();
  }

  function deleteSession()
  {
    $this->app->session->clear();
    $this->user = null;

    setcookie(_ADIOS_ID.'-user', '', 0);
    setcookie(_ADIOS_ID.'-language', '', 0);
  }

  public function signIn(array $user)
  {
    $this->user = $user;
    $this->updateUserInSession($user);
  }

  public function signOut()
  {
    $this->deleteSession();
    $this->app->router->redirectTo('?signed-out');
    exit;
  }

  public function auth()
  {
    // to be overriden
  }
}