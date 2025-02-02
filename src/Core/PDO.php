<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core;

class PDO {
  public ?\ADIOS\Core\Loader $app = null;
  public ?\PDO $connection = null;
  
  public function __construct($app) {
    $this->app = $app;
  }

  public function connect() {
    $dbHost = $this->app->configAsString('db_host');
    $dbPort = $this->app->configAsString('db_port');
    $dbUser = $this->app->configAsString('db_user');
    $dbPassword = $this->app->configAsString('db_password');
    $dbName = $this->app->configAsString('db_name');
    $dbCodepage = $this->app->configAsString('db_codepage', 'utf8mb4');

    if (!empty($dbHost)) {
      if (empty($dbName)) {
        $this->connection = new \PDO(
          "mysql:host={$dbHost};port={$dbPort};charset={$dbCodepage}",
          $dbUser,
          $dbPassword
        );
      } else {
        $this->connection = new \PDO(
          "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset={$dbCodepage}",
          $dbUser,
          $dbPassword
        );
      }
    }

  }

  public function debugQuery($query, $data = []) {
    $stmt = $this->connection->prepare($query);
    $stmt->execute($data);
    ob_start();
    $stmt->debugDumpParams();
    _var_dump(ob_get_clean());
  }

  public function execute(string $query, array $data = []): void
  {
    if (!empty($query)) {
      $stmt = $this->connection->prepare($query);
      $stmt->execute($data);
    }
  }

  public function fetchAll(string $query, array $data = [])
  {
    $stmt = $this->connection->prepare($query);
    $stmt->execute($data);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function fetchFirst(string $query, array $data = [])
  {
    $tmp = $this->fetchAll($query, $data);
    return reset($tmp);
  }

  public function startTransaction(): void
  {
    $this->execute('start transaction');
  }

  public function commit(): void
  {
    $this->execute('commit');
  }

  public function rollback(): void
  {
    $this->execute('rollback');
  }

}
