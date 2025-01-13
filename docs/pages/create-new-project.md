# Create new project

It's easy to start creating your web app with Adios.

## Prerequisities

Before starting, you need to have installed following:

  * `git`
  * `composer`
  * `node`
  * `npm`

For this guide, we are assuming that you are using `Apache` and your document root is `/var/www/html`. For `Nginx` developers, check [non-Apache web server configuration](web-server-configuration).

If you are working in different environment, some configuration will possibly need to be appropriately modified.

### Clone Adios framework

Before creating your project, you need to clone the Adios framework into a separate folder. Go to `/var/www/html` and run following:

`git clone https://github.com/wai-blue/ADIOS.git`

Now you should have `/var/www/html/ADIOS` folder with Adios framework inside.

### Create blank Adios app

Go to your app's root folder (e.g. `/var/www/html/myapp`, open `terminal` and follow these steps:

{% include 'components/step-by-step.twig' with {'steps': {

  '1': {
    'title': 'Create basic folder structure',
    'description': markdown('
Copy default folder structure from `/var/www/html/ADIOS/docs/project-template` into your app\'s root folder.

Now your app\'s root folder should contain folders and files as shown on the right.

[Here](default-folders-and-files) you can learn more about default folders and files.
'),
    'example': markdown('
```
cp -r /var/www/html/ADIOS/docs/project-template /var/www/html/myapp
```
<br/>
```
/var/www/html/myapp
  /src
  /.gitignore
  /composer.json
  /ConfigEnv.php
  /index.php
  /package.json
```
'),
  },


  '2': {
    'title': 'Install 3rd-party backend libraries',
    'description': markdown('
Adios application\'s backend requires serveral 3rd-party libraries. Check `/var/www/html/myapp/composer.json` file and modify folder paths, if needed.

When ready, run `composer install`.

When ready, you should have 3rd-party libraries for the backend available (e.g., **Laravel\'s Eloquent or Twig**).
'),
    'example': markdown('<pre>composer install</pre>'),
  },

  '3': {
    'title': 'Install 3rd-party frontend libraries',
    'description': markdown('
Adios application\'s frontend requires serveral 3rd-party libraries. Check `/var/www/html/myapp/package.json` file and modify folder paths, if needed.

When ready, run `npm i`.

Now you should have 3rd-party libraries for the frontend available (e.g. **React, Typescript, Primereact or others**).
'),
    'example': markdown('<pre>npm i</pre>'),
  },

  '4': {
    'title': 'Configure your environment',
    'description': markdown('
Open `/var/www/html/myapp/ConfigEnv.php` file and modify application name, database connection parameters, rewrite base, URLs or other environment-specific parameters.


See example of minimal configuration.
'),
    'example': markdown('
```php

// db
$config["db"] = [
  "provider" => \ADIOS\Core\Db\Providers\MySQLi::class,
];

$config["db_host"]             = "localhost";
$config["db_user"]             = "root";
$config["db_password"]         = "";
$config["db_name"]             = "myapp_db";
$config["db_codepage"]         = "utf8mb4";

$config["MyApp"]["db"]["host"] = "localhost";
$config["MyApp"]["db"]["user"] = "root";
$config["MyApp"]["db"]["password"] = "";
$config["MyApp"]["db"]["name"] = "myapp_db";

// dirs & urls
$config["rewriteBase"]         = "/myapp/";
$config["url"]                 = "http://localhost/myapp";

$config["logDir"]              = realpath(__DIR__ . "/log");
$config["tmpDir"]              = realpath(__DIR__ . "/tmp");
$config["uploadDir"]           = realpath(__DIR__ . "/upload");
$config["uploadUrl"]           = "//" . ($_SERVER["HTTP_HOST"] ?? "") . $config["rewriteBase"] . "upload";

// misc
$config["develMode"]           = true;
$config["language"]            = "en";

```
'),
  },

}} %} {# step-by-step #}

