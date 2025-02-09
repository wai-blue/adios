<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Models;

/**
 * Model for storing database operations log entries. Stored in 'db_log' SQL table.
 *
 * @package DefaultModels
 */
class DbLog extends \ADIOS\Core\Model {

  public function __construct(\ADIOS\Core\Loader $app) {
    $this->sqlName = "_db_log";
    parent::__construct($app);
  }

  public function columns(array $columns = []): array
  {
    return parent::columns([
      'ip' => ['type' => 'varchar', 'byteSize' => 35, 'title' => 'IP', 'show_column' => true],
      'action' => ['type' => 'varchar', 'byteSize' => 150, 'title' => 'Akcia', 'show_column' => true],
      'id_user' => ['type' => 'lookup', 'title' => 'Používateľ', 'model' => "ADIOS/Models/User", 'show_column' => true],
      'date' => ['type' => 'datetime', 'title' => 'Dátum', 'show_column' => true],
      'operation' => ['type' => 'varchar', 'byteSize' => 30, 'title' => 'Operácia', 'show_column' => true],
      'table_name' => ['type' => 'varchar', 'byteSize' => 150, 'title' => 'Tabuľka', 'show_column' => true],
      'row_id' => ['type' => 'int', 'title' => 'Id záznamu', 'byteSize' => '5', 'show_column' => true],
      'where_condition' => ['type' => 'text', 'interface' => 'plain_text', 'title' => 'Where podmienka', 'show_column' => true],
      'data' => ['type' => 'text', 'interface' => 'plain_text', 'title' => 'Údaje'],
      'error' => ['type' => 'text', 'interface' => 'plain_text', 'title' => 'Chyba'],
      'query' => ['type' => 'text', 'interface' => 'plain_text', 'title' => 'Požiadavka'],
      'duration' => ['type' => 'decimal', 'decimals' => '4', 'title' => 'Trvanie', 'show_column' => true],
    ]);
  }
}
