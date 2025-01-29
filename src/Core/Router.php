<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core;

class Router {
  const HTTP_GET = 'HTTP_GET';

  public ?\ADIOS\Core\Loader $app = null;

  public $routing = [];

  protected array $routesHttpGet = [];
  protected array $routeVars = [];
  
  public function __construct(\ADIOS\Core\Loader $app) {
    $this->app = $app;

    // $appControllers = \ADIOS\Core\Helper::scanDirRecursively(__DIR__ . '/../Controllers');
    // $tmpRouting = [];
    // foreach ($appControllers as $tmpController) {
    //   $tmpController = str_replace(".php", "", $tmpController);
    //   $tmpRouting["/^".str_replace("/", "\\/", $tmpController)."$/"] = [
    //     "controller" => 'ADIOS\\Controllers\\' . $tmpController,
    //   ];
    // }
    // $this->addRouting($tmpRouting);

    $this->httpGet([
      '/^api\/form\/describe\/?$/' => \ADIOS\Controllers\Api\Form\Describe::class,
      '/^api\/table\/describe\/?$/' => \ADIOS\Controllers\Api\Table\Describe::class,
      '/^api\/record\/get\/?$/' => \ADIOS\Controllers\Api\Record\Get::class,
      '/^api\/record\/get-list\/?$/' => \ADIOS\Controllers\Api\Record\GetList::class,
      '/^api\/record\/lookup\/?$/' => \ADIOS\Controllers\Api\Record\Lookup::class,
      '/^api\/record\/save\/?$/' => \ADIOS\Controllers\Api\Record\Save::class,
      '/^api\/record\/delete\/?$/' => \ADIOS\Controllers\Api\Record\Delete::class,
      '/^api\/config\/set\/?$/' => \ADIOS\Controllers\Api\Config\Set::class,
    ]);
  }

  // 2024-12-04 NEW PRINCIPLE.

  // configure routes for HTTP GET
  public function httpGet(array $routes)
  {
    $this->routesHttpGet = array_merge($this->routesHttpGet, $routes);
  }

  public function getRoutes(string $method): array
  {
    return match ($method) {
      self::HTTP_GET => $this->routesHttpGet,
      default => [],
    };
  }

  public function findController(string $method, string $route): string|null
  {
    $controller = null;

    foreach ($this->getRoutes($method) as $routePattern => $tmpRoute) {
      if (preg_match($routePattern.'i', $route, $m)) {

        if (!empty($tmpRoute['redirect'])) {
          $url = $tmpRoute['redirect']['url'];
          foreach ($m as $k => $v) {
            $url = str_replace('$'.$k, $v, $url);
          }
          $this->redirectTo($url, $tmpRoute['redirect']['code'] ?? 302);
          exit;
        } else if (is_string($tmpRoute)) {
          $controller = $tmpRoute;
        }
      }
    }

    return $controller;
  }

  public function getRouteVar($index): string
  {
    return $this->routeVars[$index] ?? '';
  }

  public function routeVarAsString($varIndex): string
  {
    return (string) ($this->routeVars[$varIndex] ?? '');
  }

  public function routeVarAsInteger($varIndex): int
  {
    return (int) ($this->routeVars[$varIndex] ?? 0);
  }

  public function routeVarAsFloat($varIndex): float
  {
    return (float) ($this->routeVars[$varIndex] ?? 0);
  }

  public function routeVarAsBool($varIndex): bool
  {
    return (bool) ($this->routeVars[$varIndex] ?? false);
  }

  public function extractRouteVariables(string $method, string $route = '')
  {
    if (empty($route)) $route = $this->app->route;

    foreach ($this->getRoutes($method) as $routePattern => $tmpRoute) {
      if (preg_match($routePattern.'i', $route, $m)) {
        $this->routeVars = $m;
        break;
      }
    }
  }


  // 2024-12-04 OLD PRINCIPLE. ALL METHODS BELOW ARE DEPRECATED.

  public function setRouting($routing) {
    if (is_array($routing)) {
      $this->routing = $routing;
    }
  }

  // DEPRECATED
  public function addRouting($routing) {
    if (is_array($routing)) {
      $this->routing = array_merge($this->routing, $routing);
    }
  }

  public function replaceRouteVariables($routeParams, $variables) {
    if (is_array($routeParams)) {
      foreach ($routeParams as $paramName => $paramValue) {

        if (is_array($paramValue)) {
          $routeParams[$paramName] = $this->replaceRouteVariables($paramValue, $variables);
        } else {
          krsort($variables);
          foreach ($variables as $k2 => $v2) {
            $routeParams[$paramName] = str_replace('$'.$k2, $v2, (string)$routeParams[$paramName]);
          }
        }
      }
    }

    return $routeParams;
  }

  public function applyRouting(string $routeUrl, array $params): array {
    $route = [];

    foreach ($this->routing as $routePattern => $tmpRoute) {
      if (preg_match($routePattern.'i', $routeUrl, $m)) {

        if (!empty($tmpRoute['redirect'])) {
          $url = $tmpRoute['redirect']['url'];
          foreach ($m as $k => $v) {
            $url = str_replace('$'.$k, $v, $url);
          }
          $this->redirectTo($url, $tmpRoute['redirect']['code'] ?? 302);
          exit;
        } else {
          $route = $tmpRoute;
          // $controller = $tmpRoute['controller'] ?? '';
          // $view = $tmpRoute['view'] ?? '';
          // $permission = $tmpRoute['permission'] ?? '';
          $tmpRoute['params'] = $this->replaceRouteVariables($tmpRoute['params'] ?? [], $m);

          foreach ($this->replaceRouteVariables($tmpRoute['params'] ?? [], $m) as $k => $v) {
            $params[$k] = $v;
          }
        }
      }
    }

    // return [$controller, $view, $permission, $params];
    return [$route, $params];
  }

  public function redirectTo(string $url, int $code = 302) {
    header("Location: " . $this->app->configAsString('accountUrl') . "/" . trim($url, "/"), true, $code);
    exit;
  }

}
