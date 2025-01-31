<?php

namespace ADIOS\Core\Db;

abstract class ColumnProperty implements \JsonSerializable
{

  protected \ADIOS\Core\Db $db;

  public function __constructor(\ADIOS\Core\Db $db)
  {
    $this->db = $db;
  }

}