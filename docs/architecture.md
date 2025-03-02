# Architecture

Adios framework is a compilation of other widely used frameworks in a way that simplifies web application development. Contains only a tiny codebase but still offers high degree of flexibility and a fast development.

It's architecture is very flat and basically is divided into two areas:

  * backend development
  * frontend development

## Backend

Basically, backend part of Adios uses **pure PHP for the MVC architecture**, **Symfony's Twig** for the HTML rendering and **Laravel's Eloquent** as a database layer, as illustrated on diagram below.

```
┌────────────────────────────────────────────────────────────────────────────┐
│ Backend architecture                                                       │
│────────────────────────────────────────────────────────────────────────────│
│ ┌───────────────────┐ ┌──────────────────────┐ ┌─────────────────────────┐ │
│ │ Pure PHP-based    │ │ HTML rendering       │ │ Database layer          │ │
│ │ MVC architecture  │ │ using Symfony's Twig │ │ with Laravel's Eloquent │ │
│ └───────────────────┘ └──────────────────────┘ └─────────────────────────┘ │
└────────────────────────────────────────────────────────────────────────────┘
Figure: Illustration of basic architecture for the backend development
```

Adios natively implements following basic features of the MVC architecture:

  * [routing](routing)
  * [models](models)
  * [controllers](controllers)

### Additional features for backend development

On top of the basic architecture, Adios provides **several additional features to simplify web application development**. These features are:

  * [user authentication](user-authentication) and [permission management](permission-management)
  * flexible [application configuration management](configuration-management)
  * powerfull [**record management**](record-management) for making CRUD (create, read, update and delete) operations
  * useful UI components (datagrids, forms and inputs), as described in the chapter about frontend development
  * [**description API**](description-api) for describing and designing UI components
  * [session management](session-management)
  * support for [translations](translations)

## Frontend

The frontend part of Adios uses **React** for rendering UI components. However, Adios applications are not purely react, they are hybrid which means that on each URL there is routing applied and a view in HTML rendered. This view can use special `<app-* />` notation which will be finally rendered to a React component.

For example, if your backend on a request to `/todo-list` URL responses with view containing `<app-table model="MyApp/Models/TodoList"></app-table>`, then a React component of class `Table` (see [Table.tsx](https://github.com/wai-blue/adios/blob/main/src/Components/Table.tsx) file) will be rendered in the browser.