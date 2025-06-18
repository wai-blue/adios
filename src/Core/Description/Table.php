<?php

namespace ADIOS\Core\Description;


class Table implements \JsonSerializable
{

  /** @property array{ title: string, subTitle: string, addButtonText: string, showHeader: bool, showFooter: bool, showFilter: bool, showHeaderTitle: bool } */
  public array $ui = [
    'title' => '',
    'subTitle' => '',
    'addButtonText' => '',
    'showHeader' => true,
    'showFooter' => true,
    'showFilter' => true,
    'showSidebarFilter' => true,
    'showHeaderTitle' => true,
  ];

  /** @property array{ canCreate: bool, canRead: bool, canUpdate: bool, canDelete: bool } */
  public array $permissions = [
    'canCreate' => false,
    'canRead' => false,
    'canUpdate' => false,
    'canDelete' => false,
  ];

  /** @property array<\ADIOS\Core\Db\Column> */
  public array $columns = [];

  /** @property array<\ADIOS\Core\Db\Input> */
  public array $inputs = [];

  public function jsonSerialize(): array
  {
    $json = [];
    $json['ui'] = $this->ui;
    $json['permissions'] = $this->permissions;
    if (count($this->columns) > 0) $json['columns'] = $this->columns;
    if (count($this->inputs) > 0) $json['inputs'] = $this->inputs;
    return $json;
  }

  public function toArray(): array
  {
    return $this->jsonSerialize();
  }

}