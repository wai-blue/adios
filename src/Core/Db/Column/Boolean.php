<?php

namespace ADIOS\Core\Db\Column;

class Boolean extends \ADIOS\Core\Db\Column
{

  protected string $type = 'boolean';

  public function __constructor(\ADIOS\Core\Db $db, string $title)
  {
    parent::__constructor($db, $title);
  }

}