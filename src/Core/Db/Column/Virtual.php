<?php

namespace ADIOS\Core\Db\Column;

use \ADIOS\Core\Db\ColumnProperty\Autocomplete;

class Virtual extends \ADIOS\Core\Db\Column
{

  protected string $type = 'virtual';
  protected int $byteSize = 255;
  protected ?Autocomplete $autocomplete = null;

  public function __construct(\ADIOS\Core\Model $model, string $title, int $byteSize = 255)
  {
    parent::__construct($model, $title);
    $this->byteSize = $byteSize;
  }

  public function sqlCreateString(string $table, string $columnName): string
  {
    return "";
  }

}