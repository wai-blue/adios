<?php

namespace ADIOS\Core\Db\Column;

class Boolean extends \ADIOS\Core\Db\Column
{

  protected string $type = 'boolean';

  public function __construct(\ADIOS\Core\Model $model, string $title)
  {
    parent::__construct($model, $title);
  }

}