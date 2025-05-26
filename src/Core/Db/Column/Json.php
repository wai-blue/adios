<?php

namespace ADIOS\Core\Db\Column;

class Json extends \ADIOS\Core\Db\Column
{

  protected string $type = 'json';
  protected string $sqlDataType = 'text';

  public function __construct(\ADIOS\Core\Model $model, string $title)
  {
    parent::__construct($model, $title);
  }

}