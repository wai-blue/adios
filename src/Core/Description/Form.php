<?php

namespace ADIOS\Core\Description;


class Form implements \JsonSerializable
{

  /** @property array{ title: string, subTitle: string, showSaveButton: boolean, showCopyButton: boolean, showDeleteButton: boolean, saveButtonText: string, addButtonText: string, copyButtonText: string, deleteButtonText: string, headerClassName: string } */
  public array $ui = [
    'title' => '',
    'subTitle' => '',
    'showSaveButton' => true,
    'showCopyButton' => false,
    'showDeleteButton' => true,
    'saveButtonText' => '',
    'addButtonText' => '',
    'copyButtonText' => '',
    'deleteButtonText' => '',
    'headerClassName' => '',
    'templateJson' => '',
  ];

  /** @property array{ canCreate: bool, canRead: bool, canUpdate: bool, canDelete: bool } */
  public array $permissions = [
    'canCreate' => false,
    'canRead' => false,
    'canUpdate' => false,
    'canDelete' => false,
  ];

  /** @property array<string, \ADIOS\Core\Description\Input> */
  public array $inputs = [];

  /** @property array<string, mixed> */
  public array $defaultValues = [];

  /** @property array<string> */
  public array $includeRelations = [];

  public function jsonSerialize(): array
  {
    $ui = [];
    if (!empty($this->ui['title'])) $ui['title'] = $this->ui['title'];
    if (!empty($this->ui['subTitle'])) $ui['subTitle'] = $this->ui['subTitle'];
    if (!empty($this->ui['saveButtonText'])) $ui['saveButtonText'] = $this->ui['saveButtonText'];
    if (!empty($this->ui['addButtonText'])) $ui['addButtonText'] = $this->ui['addButtonText'];
    if (!empty($this->ui['copyButtonText'])) $ui['copyButtonText'] = $this->ui['copyButtonText'];
    if (!empty($this->ui['deleteButtonText'])) $ui['deleteButtonText'] = $this->ui['deleteButtonText'];
    if (!empty($this->ui['headerClassName'])) $ui['headerClassName'] = $this->ui['headerClassName'];
    if (!empty($this->ui['templateJson'])) $ui['templateJson'] = $this->ui['templateJson'];
    if ($this->ui['showSaveButton']) $ui['showSaveButton'] = true;
    if ($this->ui['showCopyButton']) $ui['showCopyButton'] = true;
    if ($this->ui['showDeleteButton']) $ui['showDeleteButton'] = true;

    $permissions = [];
    if (!empty($this->permissions['canCreate'])) $permissions['canCreate'] = $this->permissions['canCreate'];
    if (!empty($this->permissions['canRead'])) $permissions['canRead'] = $this->permissions['canRead'];
    if (!empty($this->permissions['canUpdate'])) $permissions['canUpdate'] = $this->permissions['canUpdate'];
    if (!empty($this->permissions['canDelete'])) $permissions['canDelete'] = $this->permissions['canDelete'];

    $inputs = $this->inputs;
    $defaultValues = $this->defaultValues;
    $includeRelations = $this->includeRelations;

    $json = [];
    if (count($ui) > 0) $json['ui'] = $ui;
    if (count($inputs) > 0) $json['inputs'] = $inputs;
    if (count($permissions) > 0) $json['permissions'] = $permissions;
    if (count($defaultValues) > 0) $json['defaultValues'] = $defaultValues;
    if (count($includeRelations) > 0) $json['includeRelations'] = $includeRelations;

    return $json;
  }

  public function toArray(): array
  {
    return $this->jsonSerialize();
  }

}