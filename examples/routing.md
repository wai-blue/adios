# Routing

In this example we will show how to add simple routing functionality.

## Prerequisities

You should have an empty app from [this tutorial](empty-app.md).

## Create router class

Create very simple router class with only one route for *HTTP GET* method

```php
## ./src/Core/Router.php
<?php
namespace MyApp\Core;
class Router extends \ADIOS\Core\Router {
  public function __construct(\ADIOS\Core\Loader $app) {
    parent::__construct($app);
    $this->httpGet([ '/^welcome\/?$/' => \MyApp\Controllers\Welcome::class ]);
  }
}
```

## Add this router to you app

Modify `./src/App.php` as follows

```php
## ./src/App.php
<?php
class MyApp extends \ADIOS\Core\Loader {
+  public function createRouter(): \ADIOS\Core\Router {
+    return new \MyApp\Core\Router($this);
+  }
}
```

## Create controller

Create a controller for your route.

> **NOTE** This example shows dirty but simple way of creating controller which renders the content of the view directly. More clean way requires using separate views.

```php
## ./Controllers/Welcome.php
<?php
namespace MyApp\Controllers;
class Welcome extends \ADIOS\Core\Controller {
  public bool $requiresUserAuthentication = false;
  public function render(array $params): string {
    return 'Hello world.';
  }
}
```

## Run the app

  1. Run the app in the terminal. `php index.php welcome`, or
  2. Open your app in the browser. For example, navigate to: `http://localhost/my-app/welcome`

In both cases you should see following output:

```
Hello world.
```

### What happened?

When running `php index.php welcome` in terminal or opening `http://localhost/my-app/welcome` in your browser, route `welcome` has been called, parsed by the router and your welcome controller was executed. This controller rendered string 'Hello world.'

> **TIP** The `$requiresUserAuthentication` property of router controls whether rendering should be available publicly or only for authenticated user. By default it is set to `true`.