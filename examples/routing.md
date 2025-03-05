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

```php
## ./src/Controllers/Welcome.php
<?php
namespace MyApp\Controllers;
class Welcome extends \ADIOS\Core\Controller {
  public bool $requiresUserAuthentication = false;
  public function prepareView(): void {
    $this->viewParams['now'] = date('Y-m-d H:i:s');
    $this->setView('@app/Views/Welcome.twig');
  }
}
```

> **NOTE** In this controller, we set a parameter for the view and set the view to be used.

## Create view

We are going to create our first view. By default, Adios uses Twig as the rendering engine, so we must install it. In your project's folder run following:

```
composer require twig/twig
```

Now create view that we are referencing in our controller.

```html
{# ./src/View/Welcome.twig #}
Hello world. Current date and time is <b>{{ viewParams.now }}</b>.
```


## Run the app

  1. Run the app in the terminal. `php index.php welcome`, or
  2. Open your app in the browser. For example, navigate to: `http://localhost/my-app/?route=welcome`

In both cases you should see following output:

```
Hello world.
```

### What happened?

When running `php index.php welcome` in terminal or opening `http://localhost/my-app/?route=welcome` in your browser, route `welcome` has been called, parsed by the router and your welcome controller was executed.

> **TIP** The `$requiresUserAuthentication` property of router controls whether rendering should be available publicly or only for authenticated user. By default it is set to `true`.