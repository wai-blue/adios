<?php

namespace ADIOS\Core;

class Auth {
  public \ADIOS\Core\Loader $app;
  public array $params;
  protected ?array $user = null;

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
    $user = $this->getUserFromSession();
    return is_array($user);// && isset($user['id']);
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

  public function getUser(): array
  {
    return is_array($this->user) ? $this->user : [];
  }

  public function getUserRoles(): array
  {
    if (isset($this->user['ROLES']) && is_array($this->user['ROLES'])) return $this->user['ROLES'];
    else if (isset($this->user['roles']) && is_array($this->user['roles'])) return $this->user['roles'];
    else return [];
  }

  public function getUserId(): int
  {
    return (int) ($this->user['id'] ?? 0);
  }

  public function getUserLanguage(): string
  {
    $language = (string) ($this->user['language'] ?? $this->app->config->getAsString('language'));
    return (strlen($language) == 2 ? $language : 'en');
  }
}