<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Auth;

class DefaultProvider extends \ADIOS\Core\Auth {
  public $loginAttribute = 'login';
  public $passwordAttribute = 'password';
  public $activeAttribute = 'is_active';
  public $verifyMethod = 'password_verify';

  function __construct(\ADIOS\Core\Loader $app)
  {
    parent::__construct($app);

    $this->app->registerModel(\ADIOS\Models\User::class);
    $this->app->registerModel(\ADIOS\Models\UserRole::class);
    $this->app->registerModel(\ADIOS\Models\UserHasRole::class);
  }

  public function init(): void
  {
    $userLanguage = $this->getUserLanguage();
    if (empty($userLanguage)) $userLanguage = 'en';
    $this->app->config->set('language', $userLanguage);
  }

  public function createUserModel(): \ADIOS\Core\Model
  {
    return $this->app->di->create('model.user');
  }

  public function findUsersByLogin(string $login): array
  {
    return $this->createUserModel()->record
      ->orWhere($this->loginAttribute, $login)
      ->where($this->activeAttribute, '<>', 0)
      ->get()
      ->makeVisible([$this->passwordAttribute])
      ->toArray()
    ;
  }

  public function auth(): void
  {

    $userModel = $this->createUserModel();

    if ($this->isUserInSession()) {
      $this->loadUserFromSession();
    } else {
      $login = $this->app->urlParamAsString('login');
      $password = $this->app->urlParamAsString('password');
      $rememberLogin = $this->app->urlParamAsBool('rememberLogin');

      $login = trim($login);

      if (empty($login) && !empty($_COOKIE[$this->app->session->getSalt() . '-user'])) {
        $login = $userModel->authCookieGetLogin();
      }

      if (!empty($login) && !empty($password)) {
        $users = $this->findUsersByLogin($login);

        foreach ($users as $user) {
          $passwordMatch = FALSE;

          if ($this->verifyMethod == 'password_verify' && password_verify($password, $user[$this->passwordAttribute] ?? "")) {
            $passwordMatch = TRUE;
          }
          if ($this->verifyMethod == 'md5' && md5($password) == $user[$this->passwordAttribute]) {
            $passwordMatch = TRUE;
          }

          if ($passwordMatch) {
            $authResult = $userModel->loadUser($user['id']);
            $this->signIn($authResult);

            if ($rememberLogin) {
              setcookie(
                $this->app->session->getSalt() . '-user',
                $userModel->authCookieSerialize($user[$this->loginAttribute], $user[$this->passwordAttribute]),
                time() + (3600 * 24 * 30)
              );
            }

            break;

          }
        }
      }
    }

  }
}
