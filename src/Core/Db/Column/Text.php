<?php

namespace ADIOS\Core\Db\Column;

class Text extends \ADIOS\Core\Db\Column
{

  protected string $type = 'text';
  protected string $interface = 'plainText';

  public function __construct(\ADIOS\Core\Model $model, string $title, string $interface = 'plainText')
  {
    parent::__construct($model, $title);
    $this->interface = $interface;
  }

  public function getInterface(): int
  {
    return $this->interface;
  }

  public function setInterface(int $interface): Autocomplete
  {
    $this->interface = $interface;
    return $this;
  }

  public function jsonSerialize(): array
  {
    $column = parent::jsonSerialize();
    $column['interface'] = $this->interface;
    return $column;
  }

}