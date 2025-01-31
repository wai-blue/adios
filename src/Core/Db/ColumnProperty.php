<?php

namespace ADIOS\Core\Db;

abstract class ColumnProperty implements \JsonSerializable
{

  protected Column $column;

  public function __construct() { }

  public function toColumn(): Column { return $this->column; }

  public function setColumn(Column $column): void { $this->column = $column; }


}