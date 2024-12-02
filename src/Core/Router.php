<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core;

class Router {
  public ?\ADIOS\Core\Loader $app = null;

  public $routing = [];
  
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

    $this->addRouting([
      '/^api\/form\/describe\/?$/' => [ 'controller' => \ADIOS\Controllers\Api\Form\Describe::class ],
      '/^api\/table\/describe\/?$/' => [ 'controller' => \ADIOS\Controllers\Api\Table\Describe::class ],
      '/^api\/record\/get\/?$/' => [ 'controller' => \ADIOS\Controllers\Api\Record\Get::class ],
      '/^api\/record\/get-list\/?$/' => [ 'controller' => \ADIOS\Controllers\Api\Record\GetList::class ],
      '/^api\/record\/lookup\/?$/' => [ 'controller' => \ADIOS\Controllers\Api\Record\Lookup::class ],
      '/^api\/record\/save\/?$/' => [ 'controller' => \ADIOS\Controllers\Api\Record\Save::class ],
      '/^api\/record\/delete\/?$/' => [ 'controller' => \ADIOS\Controllers\Api\Record\Delete::class ],
      '/^api\/config\/set\/?$/' => [ 'controller' => \ADIOS\Controllers\Api\Config\Set::class ],
    ]);
  }

  public function setRouting($routing) {
    if (is_array($routing)) {
      $this->routing = $routing;
    }
  }

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
    header("Location: {$this->app->config['accountUrl']}/".trim($url, "/"), true, $code);
    exit;
  }

}
