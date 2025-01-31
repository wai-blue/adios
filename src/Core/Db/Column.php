<?php

namespace ADIOS\Core\Db;

abstract class Column implements \JsonSerializable
{

  protected \ADIOS\Core\Db $db;

  protected string $type = '';
  protected string $title = '';
  protected bool $readonly = false;
  protected bool $hidden = false;
  protected string $rawSqlDefinition = '';

  /** @var array<string, \ADIOS\Core\Db\ColumnProperty> */
  protected array $properties = [];

  public function __constructor(\ADIOS\Core\Db $db, string $title)
  {
    $this->db = $db;
    $this->title = $this->translate($title);
  }

  public function getType(): string { return $this->type; }
  public function setType(string $type): Column { $this->type = $type; return $this; }

  public function getTitle(): string { return $this->title; }
  public function setTitle(string $title): Column { $this->title = $title; return $this; }

  public function getReadonly(): bool { return $this->readonly; }
  public function setReadonly(bool $readonly = true): Column { $this->readonly = $readonly; return $this; }

  public function getHidden(): bool { return $this->hidden; }
  public function setHidden(bool $hidden = true): Column { $this->hidden = $hidden; return $this; }

  public function getRawSqlDefinition(): string { return $this->rawSqlDefinition; }
  public function setRawSqlDefinition(string $rawSqlDefinition): Column { $this->rawSqlDefinition = $rawSqlDefinition; return $this; }



  public function setProperty(string $name, \ADIOS\Core\Db\ColumnProperty $property): void
  {
    $this->properties[$name] = $property;
  }

  public function jsonSerialize(): array
  {
    $column = [
      'type' => $this->type,
      'title' => $this->title,
      'readonly' => $this->readonly,
    ];

    foreach ($this->properties as $name => $property) {
      $column[$name] = $property->jsonSerialize();
    }

    return $column;
  }

  public function toArray(): array
  {
    return $this->jsonSerialize();
  }

}