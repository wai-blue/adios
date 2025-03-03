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

  function __construct(\ADIOS\Core\Loader $app, array $params = [])
  {
    parent::__construct($app, $params);

    $this->app->registerModel(\ADIOS\Models\User::class);
    $this->app->registerModel(\ADIOS\Models\UserRole::class);
    $this->app->registerModel(\ADIOS\Models\UserHasRole::class);
  }

  public function createUserModel(): \ADIOS\Core\Model
  {
    return new \ADIOS\Models\User($this->app);
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
        $users = $userModel->eloquent
          ->orWhere($this->loginAttribute, $login)
          ->where($this->activeAttribute, '<>', 0)
          ->get()
          ->makeVisible([$this->passwordAttribute])
          ->toArray()
        ;

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
