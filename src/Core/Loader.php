<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core;

// Autoloader function

spl_autoload_register(function ($class) {
  $class = trim(str_replace("\\", "/", $class), "/");
  $app = \ADIOS\Core\Helper::getGlobalApp();
  $appNamespace = $app->config->getAsString('appNamespace');

  if (preg_match('/ADIOS\/([\w\/]+)/', $class, $m)) {
    @include(__DIR__ . "/../{$m[1]}.php");
  }

  if (str_starts_with($class, $appNamespace . '/')) {
    @include($app->config->getAsString('srcDir') . '/' . str_replace($appNamespace . '/', '', $class) . '.php');
  }

});

register_shutdown_function(function() {
  $error = error_get_last();
  if ($error !== null && $error['type'] == E_ERROR) {
    header('HTTP/1.1 400 Bad Request', true, 400);
  }
});

// ADIOS Loader class
class Loader
{
  const ADIOS_MODE_FULL = 1;
  const ADIOS_MODE_LITE = 2;

  const RELATIVE_DICTIONARY_PATH = '../Lang';

  public string $requestedUri = "";
  public string $controller = "";
  public string $permission = "";
  public string $uid = "";
  public string $route = "";

  // public ?\ADIOS\Core\Controller $controllerObject;

  // public bool $logged = false;

  // protected array $config = [];

  public array $modelObjects = [];
  public array $registeredModels = [];

  // public bool $userLogged = false;
  // public array $userProfile = [];
  // public array $userPasswordReset = [];

  public bool $testMode = false; // Set to TRUE only in DEVELOPMENT. Disables authentication.

  public \ADIOS\Core\Config $config;
  public \ADIOS\Core\Session $session;
  public \ADIOS\Core\Logger $logger;
  public \ADIOS\Core\Locale $locale;
  public \ADIOS\Core\Router $router;
  public \ADIOS\Core\Email $email;
  public \ADIOS\Core\Permissions $permissions;
  public \ADIOS\Core\Test $test;
  public \ADIOS\Core\Auth $auth;
  public \ADIOS\Core\Translator $translator;
  public \ADIOS\Core\PDO $pdo;

  public \Illuminate\Database\Capsule\Manager $eloquent;
  public \Twig\Environment $twig;

  public string $translationContext = '';

  /** @property array<string, string> */
  protected array $params = [];

  public ?array $uploadedFiles = null;

  public function __construct(array $config = [], int $mode = self::ADIOS_MODE_FULL)
  {

    $this->params = $this->extractParamsFromRequest();

    try {

      \ADIOS\Core\Helper::setGlobalApp($this);

      $this->config = $this->createConfigManager($config);

      if (php_sapi_name() !== 'cli') {
        if (!empty($_GET['route'])) {
          $this->requestedUri = $_GET['route'];
        } else if ($this->config->getAsString('rewriteBase') == "/") {
          $this->requestedUri = ltrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), "/");
        } else {
          $this->requestedUri = str_replace(
            $this->config->getAsString('rewriteBase'),
            "",
            parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
          );
        }

        // render static assets, if requested
        $this->renderAssets();
      }

      // inicializacia session managementu
      $this->session = $this->createSessionManager();

      // inicializacia debug konzoly
      $this->logger = $this->createLogger();

      // translator
      $this->translator = $this->createTranslator();

      // inicializacia routera
      $this->router = $this->createRouter();

      // inicializacia locale objektu
      $this->locale = $this->createLocale();

      // object pre kontrolu permissions
      $this->permissions = $this->createPermissionsManager();

      // auth provider
      $this->auth = $this->createAuthProvider();

      // test provider
      $this->test = $this->createTestProvider();

      // Twig renderer
      $this->createTwig();

      $this->pdo = new \ADIOS\Core\PDO($this);

      if ($mode == self::ADIOS_MODE_FULL) {
        $this->initDatabaseConnections();

        // start session

        $this->session->start($this->urlParamAsBool('session-persist'));

        $this->config->loadFromDB();

        foreach ($this->registeredModels as $modelName) {
          $this->getModel($modelName);
        }
      }


      $userLanguage = $this->auth->getUserLanguage();
      if (empty($userLanguage)) $userLanguage = 'en';
      $this->config->set('language', $userLanguage);

    } catch (\Exception $e) {
      echo "ADIOS INIT failed: [".get_class($e)."] ".$e->getMessage() . "\n";
      echo $e->getTraceAsString() . "\n";
      exit;
    }

    return $this;
  }

  public function isAjax(): bool
  {
    return isset($_REQUEST['__IS_AJAX__']) && $_REQUEST['__IS_AJAX__'] == "1";
  }

  public function isWindow(): bool
  {
    return isset($_REQUEST['__IS_WINDOW__']) && $_REQUEST['__IS_WINDOW__'] == "1";
  }

  public function initDatabaseConnections()
  {
    $dbHost = $this->config->getAsString('db_host', '');
    $dbPort = $this->config->getAsInteger('db_port', 3306);
    $dbName = $this->config->getAsString('db_name', '');
    $dbUser = $this->config->getAsString('db_user', '');
    $dbPassword = $this->config->getAsString('db_password', '');

    if (!empty($dbHost) && !empty($dbPort) && !empty($dbUser)) {
      $this->eloquent = new \Illuminate\Database\Capsule\Manager;
      $this->eloquent->setAsGlobal();
      $this->eloquent->bootEloquent();
      $this->eloquent->addConnection([
        "driver"    => "mysql",
        "host"      => $dbHost,
        "port"      => $dbPort,
        "database"  => $dbName ?? '',
        "username"  => $dbUser,
        "password"  => $dbPassword,
        "charset"   => 'utf8mb4',
        "collation" => 'utf8mb4_unicode_ci',
      ], 'default');

      $this->pdo->connect();
    }
  }


  public function createTestProvider(): \ADIOS\Core\Test
  {
    return new Test($this);
  }

  public function createAuthProvider(): \ADIOS\Core\Auth
  {
    return new \ADIOS\Auth\DefaultProvider($this, []);
  }

  public function createSessionManager(): \ADIOS\Core\Session
  {
    return new Session($this);
  }

  public function createConfigManager(array $config): \ADIOS\Core\Config
  {
    return new Config($this, $config);
  }

  public function createPermissionsManager(): \ADIOS\Core\Permissions
  {
    return new Permissions($this);
  }

  public function createRouter(): \ADIOS\Core\Router
  {
    return new Router($this);
  }

  public function createLogger(): \ADIOS\Core\Logger
  {
    return new Logger($this);
  }

  public function createLocale(): \ADIOS\Core\Locale
  {
    return new Locale($this);
  }

  public function createTranslator(): \ADIOS\Core\Translator
  {
    return new Translator($this);
  }
  
  public function createTwig()
  {
    if (class_exists('\\Twig\\Environment')) {
      $twigLoader = new \Twig\Loader\FilesystemLoader();
      $twigLoader->addPath($this->config->getAsString('srcDir'));
      $twigLoader->addPath($this->config->getAsString('srcDir'), 'app');

      $this->twig = new \Twig\Environment($twigLoader, array(
        'cache' => false,
        'debug' => true,
      ));

      $this->configureTwig();
    }
  }

  public function configureTwig()
  {

    $this->twig->addGlobal('config', $this->config->get());
    $this->twig->addExtension(new \Twig\Extension\StringLoaderExtension());
    $this->twig->addExtension(new \Twig\Extension\DebugExtension());

    $this->twig->addFunction(new \Twig\TwigFunction(
      'adiosModel',
      function (string $model) {
        return $this->getModel($model);
      }
    ));

    $this->twig->addFunction(new \Twig\TwigFunction(
      'adiosHtmlAttributes',
      function (?array $attributes) {
        if (!is_array($attributes)) {
          return '';
        } else {
          $attrsStr = join(
            ' ',
            array_map(
              function($key) use ($attributes) {
                if (is_bool($attributes[$key])){
                  return $attributes[$key] ? $key : '';
                } else if (is_array($attributes[$key])) {
                  return \ADIOS\Core\Helper::camelToKebab($key)."='".json_encode($attributes[$key])."'";
                } else {
                  return \ADIOS\Core\Helper::camelToKebab($key)."='{$attributes[$key]}'";
                }
              },
              array_keys($attributes)
            )
          );

          return $attrsStr;
        }
      }
    ));

    $this->twig->addFunction(new \Twig\TwigFunction(
      'str2url',
      function ($string) {
        return \ADIOS\Core\Helper::str2url($string ?? '');
      }
    ));
    $this->twig->addFunction(new \Twig\TwigFunction(
      'hasPermission',
      function (string $permission, array $idUserRoles = []) {
        return $this->permissions->granted($permission, $idUserRoles);
      }
    ));
    $this->twig->addFunction(new \Twig\TwigFunction(
      'hasRole',
      function (int|string $role) {
        return $this->permissions->hasRole($role);
      }
    ));
    $this->twig->addFunction(new \Twig\TwigFunction(
      'setTranslationContext',
      function ($context) {
        $this->translationContext = $context;
      }
    ));
    $this->twig->addFunction(new \Twig\TwigFunction(
      'translate',
      function ($string, $context = '') {
        if (empty($context)) $context = $this->translationContext;
        return $this->translate($string, [], $context);
      }
    ));
  }

  //////////////////////////////////////////////////////////////////////////////
  // MODELS

  public function registerModel(string $modelClass): void
  {
    if (!in_array($modelClass, $this->registeredModels)) {
      $this->registeredModels[] = $modelClass;
    }
  }

  public function getModelClassName($modelName): string
  {
    return str_replace("/", "\\", $modelName);
  }

  /**
   * Returns the object of the model referenced by $modelName.
   * The returned object is cached into modelObjects property.
   *
   * @param  string $modelName Reference of the model. E.g. 'ADIOS/Models/User'.
   * @throws \ADIOS\Core\Exception If $modelName is not available.
   * @return object Instantiated object of the model.
   */
  public function getModel(string $modelName): \ADIOS\Core\Model
  {
    if (!isset($this->modelObjects[$modelName])) {
      try {
        $modelClassName = $this->getModelClassName($modelName);
        $this->modelObjects[$modelName] = new $modelClassName($this);
      } catch (\Exception $e) {
        throw new \ADIOS\Core\Exceptions\GeneralException("Can't find model '{$modelName}'. ".$e->getMessage());
      }
    }

    return $this->modelObjects[$modelName];
  }

  //////////////////////////////////////////////////////////////////////////////
  // TRANSLATIONS

  public function translate(string $string, array $vars = [], string $context = "ADIOS\Core\Loader::root", $toLanguage = ""): string
  {
    return $this->translator->translate($string, $vars, $context, $toLanguage);
  }

  //////////////////////////////////////////////////////////////////////////////
  // MISCELANEOUS

  public function install() {
    $installationStart = microtime(true);

    $this->logger->info("Dropping existing tables.");

    foreach ($this->registeredModels as $modelName) {
      $model = $this->getModel($modelName);
      $model->dropTableIfExists();
    }

    $this->logger->info("Database is empty, installing models.");

    foreach ($this->registeredModels as $modelName) {
      try {
        $model = $this->getModel($modelName);

        $start = microtime(true);

        $model->install();
        $this->logger->info("Model {$modelName} installed.", ["duration" => round((microtime(true) - $start) * 1000, 2)." msec"]);
      } catch (\ADIOS\Core\Exceptions\ModelInstallationException $e) {
        $this->logger->warning("Model {$modelName} installation skipped.", ["exception" => $e->getMessage()]);
      } catch (\Exception $e) {
        $this->logger->error("Model {$modelName} installation failed.", ["exception" => $e->getMessage()]);
      } catch (\Illuminate\Database\QueryException $e) {
        //
      } catch (\ADIOS\Core\Exceptions\DBException $e) {
        // Moze sa stat, ze vytvorenie tabulky zlyha napr. kvoli
        // "Cannot add or update a child row: a foreign key constraint fails".
        // V takom pripade budem instalaciu opakovat v dalsom kole
      }
    }

    $this->logger->info("Core installation done in ".round((microtime(true) - $installationStart), 2)." seconds.");
  }

  public function extractParamsFromRequest(): array {
    $route = '';
    $params = [];

    if (php_sapi_name() === 'cli') {
      $params = @json_decode($_SERVER['argv'][2] ?? "", true);
      if (!is_array($params)) { // toto nastane v pripade, ked $_SERVER['argv'] nie je JSON string
        $params = $_SERVER['argv'];
      }
      $route = $_SERVER['argv'][1] ?? "";
    } else {
      $params = \ADIOS\Core\Helper::arrayMergeRecursively(
        array_merge($_GET, $_POST),
        json_decode(file_get_contents("php://input"), true) ?? []
      );
      unset($params['route']);
    }

    return $params;
  }

  public function extractRouteFromRequest(): string {
    $route = '';

    if (php_sapi_name() === 'cli') {
      $route = $_SERVER['argv'][1] ?? "";
    } else {
      $route = $_REQUEST['route'] ?? '';
    }

    return $route;
  }

  /**
   * Renders the requested content. It can be the (1) whole desktop with complete <html>
   * content; (2) the HTML of a controller requested dynamically using AJAX; or (3) a JSON
   * string requested dynamically using AJAX and further processed in Javascript.
   *
   * @param  mixed $params Parameters (a.k.a. arguments) of the requested controller.
   * @throws \ADIOS\Core\Exception When running in CLI and requested controller is blocked for the CLI.
   * @throws \ADIOS\Core\Exception When running in SAPI and requested controller is blocked for the SAPI.
   * @return string Rendered content.
   */
  public function render(string $route = '', array $params = []): string
  {

    try {

      // Find-out which route is used for rendering

      if (empty($route)) $route = $this->extractRouteFromRequest();
      if (count($params) == 0) $params = $this->extractParamsFromRequest();

      $this->route = $route;
      // $this->params = $params;
      $this->uploadedFiles = $_FILES;

      // Apply routing and find-out which controller, permision and rendering params will be used
      // First, try the new routing principle with httpGet
      $routeData = $this->router->parseRoute(\ADIOS\Core\Router::HTTP_GET, $this->route);

      if (empty($routeData['controller'])) {
        return '';
      } else {
        $this->controller = $routeData['controller'];
        $this->permission = '';

        $routeVars = $routeData['vars'];
        $this->router->setRouteVars($routeVars);

        foreach ($routeVars as $varName => $varValue) {
          $this->params[$varName] = $varValue;
        }
      }

      if ($this->isUrlParam('sign-out')) {
        $this->auth->signOut();
      }

      if ($this->isUrlParam('signed-out')) {
        $this->router->redirectTo('');
        exit;
      }

      // Check if controller exists and if it can be used
      if (empty($this->controller)) {
        $controllerClassName = \ADIOS\Core\Controller::class;
      } else if (!$this->controllerExists($this->controller)) {
        throw new \ADIOS\Core\Exceptions\ControllerNotFound($this->controller);
      } else {
        $controllerClassName = $this->getControllerClassName($this->controller);
      }

      // Create the object for the controller
      $controllerObject = new $controllerClassName($this);

      if (empty($this->permission) && !empty($controllerObject->permission)) {
        $this->permission = $controllerObject->permission;
      }

      // Perform some basic checks
      if (php_sapi_name() === 'cli') {
        if (!$controllerClassName::$cliSAPIEnabled) {
          throw new \ADIOS\Core\Exceptions\GeneralException("Controller is not enabled in CLI interface.");
        }
      } else {
        if (!$controllerClassName::$webSAPIEnabled) {
          throw new \ADIOS\Core\Exceptions\GeneralException("Controller is not enabled in WEB interface.");
        }
      }

      if (!$this->testMode && $controllerObject->requiresUserAuthentication) {
        $this->auth->auth();
        $this->config->filterByUser();
        if (!$this->auth->isUserInSession()) {
          $controllerObject = $this->router->createSignInController();
          $this->permission = $controllerObject->permission;
        }
      }

      if (!$this->testMode && $controllerObject->requiresUserAuthentication) {
        $this->permissions->check($this->permission);
      }

      $controllerObject->preInit();
      $controllerObject->init();
      $controllerObject->postInit();

      // All OK, rendering content...

      // vygenerovanie UID tohto behu
      if (empty($this->uid)) {
        $uid = $this->getUid($this->urlParamAsString('id'));
      } else {
        $uid = $this->uid.'__'.$this->getUid($this->urlParamAsString('id'));
      }

      $this->setUid($uid);

      $return = '';

      unset($this->params['__IS_AJAX__']);

      $this->onBeforeRender();

      // Either return JSON string ...
      if ($controllerObject->returnType == Controller::RETURN_TYPE_JSON) {
        try {
          $returnArray = $controllerObject->renderJson();
        } catch (\Throwable $e) {
          http_response_code(400);

          $returnArray = [
            'status' => 'error',
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
          ];
        }
        $return = json_encode($returnArray);
      } elseif ($controllerObject->returnType == Controller::RETURN_TYPE_STRING) {
        $return = $controllerObject->renderString();
      } elseif ($controllerObject->returnType == Controller::RETURN_TYPE_NONE) {
        $controllerObject->run();
        $return = '';
      } else {
        $controllerObject->prepareView();

        $view = $controllerObject->getView() === '' ? $this->view : $controllerObject->getView();

        $contentParams = [
          'app' => $this,
          'uid' => $this->uid,
          'user' => $this->auth->getUser(),
          'config' => $this->config->get(),
          'routeUrl' => $this->route,
          'routeParams' => $this->params,
          'route' => $this->route,
          'session' => $this->session->get(),
          'controller' => $controllerObject,
          'viewParams' => $controllerObject->getViewParams(),
          'windowParams' => $controllerObject->getViewParams()['windowParams'] ?? null,
        ];

        if ($view !== null) {
          $contentHtml = $controllerObject->renderer->render(
            $view,
            $contentParams
          );
        } else {
          $contentHtml = $controllerObject->render($contentParams);
        }

        // In some cases the result of the view will be used as-is ...
        if (php_sapi_name() == 'cli' || $this->urlParamAsBool('__IS_AJAX__') || $controllerObject->hideDefaultDesktop) {
          $html = $contentHtml;

        // ... But in most cases it will be "encapsulated" in the desktop.
        } else {
          $desktopControllerObject = $this->router->createDesktopController();
          $desktopControllerObject->prepareView();

          if (isset($desktopControllerObject->renderer) && !empty($desktopControllerObject->getView())) {
            $desktopParams = $contentParams;
            $desktopParams['viewParams'] = array_merge($desktopControllerObject->getViewParams(), $contentParams['viewParams']);
            $desktopParams['contentHtml'] = $contentHtml;

            $html = $desktopControllerObject->renderer->render(
              $desktopControllerObject->getView(),
              $desktopParams
            );
          } else {
            $html = $contentHtml;
          }

        }

        $return = $html;
      }

      $this->onAfterRender();

      return $return;

    } catch (\ADIOS\Core\Exceptions\ControllerNotFound $e) {
      header('HTTP/1.1 400 Bad Request', true, 400);
      return $this->renderFatal('Controller not found: ' . $e->getMessage(), false);
    } catch (\ADIOS\Core\Exceptions\NotEnoughPermissionsException $e) {
      $message = $e->getMessage();
      if ($this->auth->isUserInSession()) {
        $message .= " Hint: Sign out and sign in again. {$this->config->getAsString('accountUrl')}?sign-out";
      }
      return $this->renderFatal($message, false);
      // header('HTTP/1.1 401 Unauthorized', true, 401);
    } catch (\ADIOS\Core\Exceptions\GeneralException $e) {
      header('HTTP/1.1 400 Bad Request', true, 400);
      return "ADIOS RUN failed: [".get_class($e)."] ".$e->getMessage();
    } catch (\ArgumentCountError $e) {
      echo $e->getMessage();
      header('HTTP/1.1 400 Bad Request', true, 400);
    } catch (\Exception $e) {
      if ($this->testMode) {
        throw new (get_class($e))($e->getMessage());
      } else {
        $error = error_get_last();

        if ($error && $error['type'] == E_ERROR) {
          $return = $this->renderFatal(
            '<div style="margin-bottom:1em;">'
              . $error['message'] . ' in ' . $error['file'] . ':' . $error['line']
            . '</div>'
            . '<pre style="font-size:0.75em;font-family:Courier New">'
              . $e->getTraceAsString()
            . '</pre>',
            true
          );
        } else {
          $return = $this->renderFatal($this->renderExceptionHtml($e));
        }

        return $return;

        if (php_sapi_name() !== 'cli') {
          header('HTTP/1.1 400 Bad Request', true, 400);
        }
      }
    }
  }

  public function getControllerClassName(string $controller) : string {
    return '\\' . trim(str_replace('/', '\\', $controller), '\\');
  }

  public function controllerExists(string $controller) : bool {
    return class_exists($this->getControllerClassName($controller));
  }

  public function renderAssets() {
    $cachingTime = 3600;
    $headerExpires = "Expires: ".gmdate("D, d M Y H:i:s", time() + $cachingTime)." GMT";
    $headerCacheControl = "Cache-Control: max-age={$cachingTime}";

    if ($this->requestedUri == "adios/cache.css") {
      $cssCache = $this->renderCSSCache();

      header("Content-type: text/css");
      header("ETag: ".md5($cssCache));
      header($headerExpires);
      header("Pragma: cache");
      header($headerCacheControl);

      echo $cssCache;

      exit();
    } else if ($this->requestedUri == "adios/cache.js") {
      $jsCache = $this->renderJSCache();
      $cachingTime = 3600;

      header("Content-type: application/x-javascript");
      header("ETag: ".md5($jsCache));
      header($headerExpires);
      header("Pragma: cache");
      header($headerCacheControl);

      echo $jsCache;

      exit();
    }
  }

  public function renderSuccess($return) {
    return json_encode([
      "result" => "success",
      "message" => $return,
    ]);
  }

  public function renderWarning($message, $isHtml = true) {
    if ($this->isAjax() && !$this->isWindow()) {
      return json_encode([
        "status" => "warning",
        "message" => $message,
      ]);
    } else {
      return "
        <div class='alert alert-warning' role='alert'>
          ".($isHtml ? $message : htmlspecialchars($message))."
        </div>
      ";
    }
  }

  public function renderFatal($message, $isHtml = true) {
    if ($this->isAjax() && !$this->isWindow()) {
      return json_encode([
        "status" => "error",
        "message" => $message,
      ]);
    } else {
      return "
        <div class='alert alert-danger' role='alert' style='z-index:99999999'>
          ".($isHtml ? $message : htmlspecialchars($message))."
        </div>
      ";
    }
  }

  public function renderHtmlFatal($message) {
    return $this->renderFatal($message, true);
  }

  public function renderExceptionHtml($exception) {

    $traceLog = "";
    foreach ($exception->getTrace() as $item) {
      $traceLog .= "{$item['file']}:{$item['line']}\n";
    }

    $errorMessage = $exception->getMessage();
    $errorHash = md5(date("YmdHis").$errorMessage);

    $errorDebugInfoHtml =
      "Error hash: {$errorHash}<br/>"
      . "<br/>"
      . "<div style='color:#888888'>"
        . get_class($exception) . "<br/>"
        . "Stack trace:<br/>"
        . "<div class='trace-log'>{$traceLog}</div>"
      . "</div>"
    ;

    $this->logger->error("{$errorHash}\t{$errorMessage}");

    switch (get_class($exception)) {
      case 'ADIOS\Core\Exceptions\DBException':
        $html = "
          <div class='adios exception emoji'>🥴</div>
          <div class='adios exception message'>
            Oops! Something went wrong with the database.
          </div>
          <div class='adios exception message'>
            {$errorMessage}
          </div>
          {$errorDebugInfoHtml}
        ";
      break;
      case 'Illuminate\Database\QueryException':
      case 'ADIOS\Core\Exceptions\DBDuplicateEntryException':

        if (get_class($exception) == 'Illuminate\Database\QueryException') {
          $dbQuery = $exception->getSql();
          $dbError = $exception->errorInfo[2];
          $errorNo = $exception->errorInfo[1];
        } else {
          list($dbError, $dbQuery, $initiatingModelName, $errorNo) = json_decode($exception->getMessage(), true);
        }

        $invalidColumns = [];

        if (!empty($initiatingModelName)) {
          $initiatingModel = $this->getModel($initiatingModelName);
          $columns = $initiatingModel->columns;
          $indexes = $initiatingModel->indexes();

          preg_match("/Duplicate entry '(.*?)' for key '(.*?)'/", $dbError, $m);
          $invalidIndex = $m[2];
          $invalidColumns = [];
          foreach ($indexes[$invalidIndex]['columns'] as $columnName) {
            $invalidColumns[] = $columns[$columnName]->getTitle();
          }
        } else {
          preg_match("/Duplicate entry '(.*?)' for key '(.*?)'/", $dbError, $m);
          if (!empty($m[2])) $invalidColumns = [$m[2]];
        }

        switch ($errorNo) {
          case 1216:
          case 1451:
            $errorMessage = "You cannot delete record that is linked with another records. Delete the linked records first.";
          break;
          case 1062:
          case 1217:
          case 1452:
            $errorMessage = "You are trying to save a record that is already existing.";
          break;
          default:
            $errorMessage = $dbError;
          break;
        }

        $html = "
          <div class='adios exception message'>
            ".$this->translate($errorMessage)."<br/>
            <br/>
            <b>".join(", ", $invalidColumns)."</b>
          </div>
          <a class='btn btn-small btn-transparent' onclick='$(this).next(\"pre\").slideToggle();'>
            <span class='text'>" . $this->translate('Show error details') . "</span>
          </a>
          <pre style='font-size:9px;text-align:left;display:none;padding-top:1em'>{$errorDebugInfoHtml}</pre>
        ";
      break;
      default:
        $html = "
          <div class='adios exception message'>
            Oops! Something went wrong.
          </div>
          <div class='adios exception message'>
            ".$exception->getMessage()."
          </div>
          {$errorDebugInfoHtml}
        ";
      break;
    }

    return $html;//$this->renderHtmlWarning($html);
  }

  public function renderHtmlWarning($warning) {
    return $this->renderWarning($warning, true);
  }

  ////////////////////////////////////////////////
  // metody pre pracu s konfiguraciou

  public function onBeforeRender(): void
  {
    // to be overriden
  }

  public function onAfterRender(): void
  {
    // to be overriden
  }

  ////////////////////////////////////////////////

  public function getUid($uid = '') {
    if (empty($uid)) {
      $tmp = $this->controller.'-'.time().rand(100000, 999999);
    } else {
      $tmp = $uid;
    }

    $tmp = str_replace('\\', '/', $tmp);
    $tmp = str_replace('/', '-', $tmp);

    $uid = "";
    for ($i = 0; $i < strlen($tmp); $i++) {
      if ($tmp[$i] == "-") {
        $uid .= strtoupper($tmp[++$i]);
      } else {
        $uid .= $tmp[$i];
      }
    }

    $this->setUid($uid);

    return $uid;
  }

  /**
   * Checks the argument whether it is a valid ADIOS UID string.
   *
   * @param  string $uid The string to validate.
   * @throws \ADIOS\Core\Exceptions\InvalidUidException If the provided string is not a valid ADIOS UID string.
   * @return void
   */
  public function checkUid($uid) {
    if (preg_match('/[^A-Za-z0-9\-_]/', $uid)) {
      throw new \ADIOS\Core\Exceptions\InvalidUidException();
    }
  }

  public function setUid($uid) {
    $this->checkUid($uid);
    $this->uid = $uid;
  }

  public function renderCSSCache() {
    $css = "";

    $cssFiles = [
      dirname(__FILE__)."/../Assets/Css/fontawesome-5.13.0.css",
      dirname(__FILE__)."/../Assets/Css/bootstrap.min.css",
      dirname(__FILE__)."/../Assets/Css/sb-admin-2.css",
      dirname(__FILE__)."/../Assets/Css/components.css",
      dirname(__FILE__)."/../Assets/Css/colors.css",
    ];

    foreach ($cssFiles as $file) {
      $css .= @file_get_contents($file)."\n";
    }

    return $css;
  }

  // private function scanReactFolder(string $path): string {
  //   $reactJs = '';

  //   foreach (scandir($path . '/Assets/Js/React') as $file) {
  //     if ('.js' == substr($file, -3)) {
  //       $reactJs = @file_get_contents($path . "/Assets/Js/React/{$file}") . ";";
  //       break;
  //     }
  //   }

  //   return $reactJs;
  // }

  public function renderJSCache() {
    $js = "";

    $jsFiles = [
      dirname(__FILE__)."/../Assets/Js/adios.js",
      dirname(__FILE__)."/../Assets/Js/ajax_functions.js",
      dirname(__FILE__)."/../Assets/Js/base64.js",
      dirname(__FILE__)."/../Assets/Js/cookie.js",
      dirname(__FILE__)."/../Assets/Js/desktop.js",
      dirname(__FILE__)."/../Assets/Js/jquery-3.5.1.js",
      dirname(__FILE__)."/../Assets/Js/md5.js",
      dirname(__FILE__)."/../Assets/Js/moment.min.js",
    ];


    foreach ($jsFiles as $file) {
      $js .= (string) @file_get_contents($file) . ";\n";
    }

    return $js;
  }





  public function getUrlParams(): array
  {
    return $this->params;
  }

  public function isUrlParam(string $paramName): bool
  {
    return isset($this->params[$paramName]);
  }

  public function urlParamNotEmpty(string $paramName): bool
  {
    return $this->isUrlParam($paramName) && !empty($this->params[$paramName]);
  }

  public function setUrlParam(string $paramName, string $newValue): void
  {
    $this->params[$paramName] = $newValue;
  }

  public function removeUrlParam(string $paramName): void
  {
    if (isset($this->params[$paramName])) unset($this->params[$paramName]);
  }

  public function urlParamAsString(string $paramName, string $defaultValue = ''): string
  {
    if (isset($this->params[$paramName])) return (string) $this->params[$paramName];
    else return $defaultValue;
  }

  public function urlParamAsInteger(string $paramName, int $defaultValue = 0): int
  {
    if (isset($this->params[$paramName])) return (int) $this->params[$paramName];
    else return $defaultValue;
  }

  public function urlParamAsFloat(string $paramName, float $defaultValue = 0): float
  {
    if (isset($this->params[$paramName])) return (float) $this->params[$paramName];
    else return $defaultValue;
  }

  public function urlParamAsBool(string $paramName, bool $defaultValue = false): bool
  {
    if (isset($this->params[$paramName])) {
      if (strtolower($this->params[$paramName]) === 'false') return false;
      else return (bool) $this->params[$paramName];
    } else return $defaultValue;
  }

  /**
  * @return array<string, string>
  */
  public function urlParamAsArray(string $paramName, array $defaultValue = []): array
  {
    if (isset($this->params[$paramName])) return (array) $this->params[$paramName];
    else return $defaultValue;
  }

  public function getLanguage(): string
  {
    $user = (isset($this->auth) ? $this->auth->getUserFromSession() : []);
    if (isset($user['language']) && strlen($user['language']) == 2) {
      return $user['language'];
    } else if (isset($_COOKIE['language']) && strlen($_COOKIE['language']) == 2) {
      return $_COOKIE['language'];
    } else {
      $language = $this->config->getAsString('language', 'en');
      if (strlen($language) !== 2) $language = 'en';
      return $language;
    }
  }



  public static function getDictionaryFilename(string $language): string
  {
    if (strlen($language) == 2) {
      $appClass = get_called_class();
      $reflection = new \ReflectionClass(get_called_class());
      $rootFolder = pathinfo((string) $reflection->getFilename(), PATHINFO_DIRNAME);
      return $rootFolder . '/' . static::RELATIVE_DICTIONARY_PATH . '/' . $language . '.json';
    } else {
      return '';
    }
  }

  /**
  * @return array|array<string, array<string, string>>
  */
  public static function loadDictionary(string $language): array
  {
    $dict = [];
    $dictFilename = static::getDictionaryFilename($language);
    if (is_file($dictFilename)) $dict = (array) @json_decode((string) file_get_contents($dictFilename), true);
    return $dict;
  }

  /**
  * @return array|array<string, array<string, string>>
  */
  public static function addToDictionary(string $language, string $contextInner, string $string): void
  {
    $dictFilename = static::getDictionaryFilename($language);
    if (is_file($dictFilename)) {
      $dict = static::loadDictionary($language);
      $dict[$contextInner][$string] = '';
      file_put_contents($dictFilename, json_encode($dict, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
  }


}
