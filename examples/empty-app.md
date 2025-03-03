# Empty app

In this example we will show how to create and empty Adios app.

## Prepare the development environment

  * check if you have PHP 8.x
  * create root folder for your app: `mkdir /var/www/html/my-app`
  * go to the folder: `cd /var/www/html/my-app`
  * install Adios (no dependencies required): `composer require wai-blue/adios`

## Create your app loader

Create very simple app loader class. Leave it empty for now.

```php
## ./src/app.php
<?php
class MyApp extends \ADIOS\Core\Loader { }
```

## Create configuration file

You'll need at least two configuration parameters: `appNamespace` and `dir`.

```php
## ./scr/env.php
<?php
$config = [
  'appNamespace' => 'MyApp',
  'dir' => __DIR__,
];
```

## Create index.php

Load your class, environment config and render your app.

```php
## ./index.php
<?php
// load config, composer's autoloaders and app loader class
require_once("env.php");
require_once("vendor/autoload.php");
require_once("src/app.php");

// create loader and render default output
echo (new MyApp($config))->render();
```

## Run the app

  1. Run the app in the terminal. `php index.php about`, or
  2. Open your app in the browser. For example, navigate to: `http://localhost/my-app/about`

In both cases you should see following output:

```
This is Adios application.
```

### What happened?

When running `php index.php about` in terminal or opening `http://localhost/my-app/about` in your browser, route `about` has been called, parsed by the router and a default controller [About.php](../src/Controllers/About.php) was executed. This controller rendered string containg information about your app.

> **TIP** There are some other default routes, check [Router.php](../src/Core/Router.php).

## Add more functionality

You have your empty Adios app ready. Now you can:

  * configure **routing**
  * create **models, controllers or views**
  * add a **rendering engine** (default rendering engine is [Symfony's Twig](https://twig.symfony.com))
  * add a **database layer** (default database layer is [Laravel's Eloquent](https://laravel.com/docs/11.x/eloquent)) and connect to database
  * use built-in **React components** ([Table.tsx](../src/Components/Table.tsx), [Form.tsx](../src/Components/Input.tsx) or various [inputs](../src/Components/Inputs))