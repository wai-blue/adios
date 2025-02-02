<?php

namespace ADIOS\Core\Description;


class Form implements \JsonSerializable
{

  /** @property array{ title: string, subTitle: string, saveButtonText: string, addButtonText: string, copyButtonText: string, deleteButtonText: string, headerClassName: string } */
  public array $ui = [
    'title' => '',
    'subTitle' => '',
    'saveButtonText' => '',
    'addButtonText' => '',
    'copyButtonText' => '',
    'deleteButtonText' => '',
    'headerClassName' => '',
  ];

  /** @property array{ canCreate: bool, canRead: bool, canUpdate: bool, canDelete: bool } */
  public array $permissions = [
    'canCreate' => false,
    'canRead' => false,
    'canUpdate' => false,
    'canDelete' => false,
  ];

  /** @property array<string, \ADIOS\Core\Db\Column> */
  public array $columns = [];

  /** @property array<string, mixed> */
  public array $defaultValues = [];

  /** @property array<string> */
  public array $includeRelations = [];

  public function jsonSerialize(): array
  {
    return [
      'ui' => $this->ui,
      'columns' => $this->columns,
      'permissions' => $this->permissions,
      'defaultValues' => $this->defaultValues,
    ];
  }

  public function toArray(): array
  {
    return $this->jsonSerialize();
  }

}