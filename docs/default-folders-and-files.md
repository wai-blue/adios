# Default folders and files

Adios app's default folders and files are following:

```
/var/www/html/myapp
  /bin                               # compiled source files
  /log                               # all logs are stored here
  /node_modules                      # all Node.js libraries are stored here
  /src
    /priv                            # private source files (e.g. React components or TailwindCSS source). These files shall not be put in the production environment)
      /Components
        /index.tsx                   # entry point for Webpack, Node.js and React
      App.css                        # entry point for TailwindCss
    /publ                            # public source files (e.g. PHP files). These files must be present in the production environment.
      /Core
        Controller.php               # app's core controller (will be overriden by other controllers)
        Model.php                    # app's core model (will be overriden by other models)
        Permissions.php              # app's permissions handler
        Router.php                   # app's router
      /Modules                       # app's MVC-related files (models, views, controllers)
      /Views
        Desktop.twig                 # app's desktop UI
        SignIn.twig                  # app's sign-in form
    ConfigApp.php                    # app configuration (independent from the environment)
    App.php                          # main application class (instantiated in index.php)
  /upload                            # all files and images are uploaded here
  /vendor                            # all Composer-downloaded libraries are stored here

  .gitignore                         # default .gitignore file
  .htacess                           # for Apache users
  babel.config.js                    # default Babel configuration
  ConfigEnv.php                      # configuration of the app's environment (you may want to have different envs for the same app)
  composer.json                      # default composer.json file
  index.php                          # app's entry point
  package.json                       # default package.json file for Node and NPM
  tailwind.config.js                 # default TailwindCss configuration
  tsconfig.js                        # default TypeScript configuration
  webpack.config.js                  # default Webpack config
```

## Usage

### Intensity of development

During the development of the app's functionality, mostly following files and folders are updated:

  * For **back-end** development:
    * `src/publ/Modules/*.*` - MVC's models, views and controllers
    * `src/publ/Core/*.*` - when creating or update routes or permissions
  * For **front-end** development:
    * `src/priv/Components/*.tsx` - React components for UI
    * `src/priv/App.css` - TailwindCSS-based definition of CSS styles