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

  public function jsonSerialize(): array
  {
    return [
      'ui' => $this->ui,
      'columns' => $this->columns,
      'permissions' => $this->permissions,
    ];
  }

  public function toArray(): array
  {
    return $this->jsonSerialize();
  }

}