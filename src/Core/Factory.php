<?php

namespace ADIOS\Core;

class Factory {
  public static function create(string $class, array $args = [])
  {
    $app = \ADIOS\Core\Helper::getGlobalApp();
    $classBackslash = str_replace('/', '\\', $class);
    $coreClasses = $app->configAsArray('coreClasses');
    $coreClass = $coreClasses[$class] ?? ($coreClasses[$classBackslash] ?? '');

    $classConverted = empty($coreClass) ? '\\ADIOS\\' . $classBackslash : $coreClass;

    return (new \ReflectionClass($classConverted))->newInstanceArgs($args);
  }
}