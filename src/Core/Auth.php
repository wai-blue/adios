<?php

namespace ADIOS\Core;

class Auth {
  public \ADIOS\Core\Loader $app;
  protected ?array $user = null;

  function __construct(\ADIOS\Core\Loader $app)
  {
    $this->app = $app;
  }

  public function getUserFromSession(): array
  {
    $tmp = $this->app->session->get('userProfile') ?? [];
    return [
      'id' => (int) ($tmp['id'] ?? 0),
      'login' => (string) ($tmp['login'] ?? ''),
      'is_active' => (bool) ($tmp['is_active'] ?? false),
    ];
  }

  public function updateUserInSession(array $user): void
  {
    $this->app->session->set('userProfile', $user);
  }

  public function isUserInSession(): bool
  {
    $user = $this->getUserFromSession();
    return isset($user['id']) && $user['id'] > 0;
  }

  public function loadUserFromSession()
  {
    $this->user = $this->getUserFromSession();
  }

  function deleteSession()
  {
    $this->app->session->clear();
    $this->user = null;

    setcookie($this->app->session->getSalt() . '-user', '', 0);
    setcookie($this->app->session->getSalt() . '-language', '', 0);
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

  public function auth(): void
  {
    // to be overriden
  }

  /**
   * Generates and manages optional forgot password functionality
   * @return void
   */
  public function forgotPassword(): void
  {
    // to be overriden
  }

  /**
   * Manages resetting passwords
   * @return void
   */
  public function resetPassword(): void
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

  public function userHasRole(int $idRole): bool
  {
    return in_array($idRole, $this->getUserRoles());
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