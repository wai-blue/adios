<?php

namespace ADIOS\Core\Db\Column;

class DateTime extends \ADIOS\Core\Db\Column
{

  protected string $type = 'datetime';

  public function __constructor(\ADIOS\Core\Db $db, string $title)
  {
    parent::__constructor($db, $title);
  }

}