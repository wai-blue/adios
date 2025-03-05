# Routing

In this example we will show how to add simple user management feature.

## Prerequisities

You should have an empty app with simple router from [this tutorial](routing.md)

## Add route for user management

Add following lines to your router:

```php
## ./src/Core/Router.php
<?php
namespace MyApp\Core;
class Router extends \ADIOS\Core\Router {
  public function __construct(\ADIOS\Core\Loader $app) {
    parent::__construct($app);
    $this->httpGet([ '/^welcome\/?$/' => \MyApp\Controllers\Welcome::class ]);
+    $this->httpGet([ '/^users\/?$/' => \MyApp\Controllers\Users::class ]);
  }
}
```

## Create controller

We will only set the view to be rendered.

```php
## ./src/Controllers/Users.php
<?php
namespace MyApp\Controllers;
class Users extends \ADIOS\Core\Controller {
  public bool $requiresUserAuthentication = false;
  public function prepareView(): void {
    $this->setView('@app/Views/Users.twig');
  }
}
```

## Install and configure Javascript and CSS libraries

In this example, we are going to use the [Table.tsx](../src/Components/Table.tsx) component to list all users. This is a react component and uses also some other Javascript libraries. So, we need to **install and build Javascript and CSS libraries** now.

Run following commands in you project's folder:

```
npm install react react-select react-dom tailwindcss primereact webpack webpack-cli
npm install babel-loader @babel/preset-react @babel/preset-typescript style-loader css-loader sass-loader sass
npm install uuid axios @uiw/react-color-compact moment react-images-uploading react-tag-input sweetalert2 notyf
npm install react-flatpickr --force
```

### Configure TailwindCSS, Typescript, Webpack and other libs

Create `./tailwind.config.js` file:

```js
/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'selector',
  content: [
    "./src/**/*.{html,js,twig,tsx}",
    "./vendor/wai-blue/adios/**/*.{tsx,twig}",
    "./vendor/wai-blue/adios/node_modules/primereact/**/*.{js,ts,jsx,tsx}",
  ]
}
```

Create `./tsconfig.json` file:

```json
{
  "compilerOptions": {
    "moduleResolution": "node",
    "esModuleInterop": true,
    "jsx": "react-jsx",
    "preserveSymlinks": true,
    "baseUrl": "."
  },
  "include": ["./**/*"]
}
```

Create `./webpack.config.json` file:

```js
const path = require('path');

module.exports = (env, arg) => {
  return {
    entry: {
      app: ['./src/App.tsx'],
    },
    output: {
      path: path.resolve(__dirname, 'assets'),
      filename: '[name].js',
      clean: true
    },
    module: {
      rules: [
        {
          test: /\.(js|mjs|jsx|ts|tsx)$/,
          exclude: /node_modules/,
          use: 'babel-loader',
        },
        {
          test: /\.(scss|css)$/,
          use: ['style-loader', 'css-loader', 'sass-loader'],
        }
      ],
    },
    resolve: {
      modules: [ path.resolve(__dirname, './node_modules') ],
      extensions: ['.js', '.jsx', '.ts', '.tsx', '.scss', '.css'],
    }
  }
};
```

Create `babel.config.js` file:

```js
module.exports = function (api) {
  api.cache(true);

  const presets = ["@babel/preset-react", "@babel/preset-typescript"];
  const plugins = [];

  return {
    presets,
    plugins
  };
}
```

Modify `./package.json` and add a script for `build` command:

```js
  "dependencies": {
+    "adios": "file:./vendor/wai-blue/adios/src/Components",
  },
+  "scripts": {
+    "build": "npx webpack --mode development && npx @tailwindcss/cli -i ./src/App.twcss -o ./assets/app.css"
+  },
```

And run `npm update` to apply changes in dependecies.

### Create entry files for Javascript and CSS

Create `./src/App.tsx` file:

```js
import 'primereact/resources/themes/lara-light-teal/theme.css';
import { ADIOS } from "adios/Loader";

class MyApp extends ADIOS { }

const app: MyApp = new MyApp({'accountUrl': 'http://localhost/adios-test-app'});

globalThis.app = app;
globalThis.app.renderReactElements();
```

Create `./src/App.twcss` file:

```css
@layer theme, base, components, utilities, primereact, adios, app;

@theme {
  --color-primary: #008000;
  --color-secondary: #7FB562;
  
  --spacing-8xl: 96rem;
  --spacing-9x': 128rem;

  --radius-4xl: 2rem;
}

@import "tailwindcss";
@import "../vendor/wai-blue/adios/src/Assets/Css/desktop.css";
@import "../vendor/wai-blue/adios/src/Assets/Css/components.css";
@import "../vendor/wai-blue/adios/src/Assets/Css/responsive.css";
@import "../vendor/wai-blue/adios/src/Assets/Css/primereact.css";
@import "../vendor/wai-blue/adios/src/Assets/Css/skin.css";
@import "../vendor/wai-blue/adios/src/Assets/Css/color-scales.css";

@layer app {
  /* add your custom css here */
}
```

### Build everything together

Run `npm run build` in your project's root folder to build everything together.

## Create view

Now we have our UI components ready. Create the view and use them.

```html
{# ./src/View/Users.twig #}
<html>
<head>
  <script src='{{ config.accountUrl }}/?route=adios/cache.js'></script>
  <script defer src='{{ config.url }}/assets/app.js'></script>
  <link rel="stylesheet" type="text/css" href="{{ config.url }}/assets/app.css">
</head>
<body>
  <app-table model="ADIOS/Models/User"></app-table>
</body>
</html>
```

## Run the app

  1. Run the app in the terminal. `php index.php welcome`, or
  2. Open your app in the browser. For example, navigate to: `http://localhost/my-app/?route=welcome`

In both cases you should see following output:

```
Hello world.
```

### What happened?

When running `php index.php welcome` in terminal or opening `http://localhost/my-app/?route=welcome` in your browser, route `welcome` has been called, parsed by the router and your welcome controller was executed. This controller rendered string 'Hello world.'

> **TIP** The `$requiresUserAuthentication` property of router controls whether rendering should be available publicly or only for authenticated user. By default it is set to `true`.