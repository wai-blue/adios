<?php

namespace ADIOS\Core\Db\Column;

class PrimaryKey extends Integer
{

  protected string $rawSqlDefinition = 'primary key auto_increment';
  protected bool $readonly = true;

}