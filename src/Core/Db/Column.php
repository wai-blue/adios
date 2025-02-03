<?php

namespace ADIOS\Core\Db;

abstract class Column implements \JsonSerializable
{

  protected \ADIOS\Core\Model $model;

  protected string $type = '';
  protected string $sqlDataType = '';
  protected string $title = '';
  protected bool $readonly = false;
  protected bool $required = false;
  protected bool $hidden = false;
  protected string $rawSqlDefinition = '';
  protected string $inputComponent = '';
  protected string $unit = '';
  protected string $format = '';
  protected string $description = '';
  protected mixed $defaultValue = null;

  /** @var array<string, \ADIOS\Core\Db\ColumnProperty> */
  protected array $properties = [];

  public function __construct(\ADIOS\Core\Model $model, string $title)
  {
    $this->model = $model;
    $this->title = $title;
  }

  public function getType(): string { return $this->type; }
  public function setType(string $type): Column { $this->type = $type; return $this; }

  public function getSqlDataType(): string { return $this->sqlDataType; }
  public function setSqlDataType(string $sqlDataType): Column { $this->sqlDataType = $sqlDataType; return $this; }

  public function getTitle(): string { return $this->title; }
  public function setTitle(string $title): Column { $this->title = $title; return $this; }

  public function getReadonly(): bool { return $this->readonly; }
  public function setReadonly(bool $readonly = true): Column { $this->readonly = $readonly; return $this; }

  public function getRequired(): bool { return $this->required; }
  public function setRequired(bool $required = true): Column { $this->required = $required; return $this; }

  public function getUnit(): bool { return $this->unit; }
  public function setUnit(bool $unit = true): Column { $this->unit = $unit; return $this; }

  public function getFormat(): bool { return $this->format; }
  public function setFormat(bool $format = true): Column { $this->format = $format; return $this; }

  public function getDescription(): bool { return $this->description; }
  public function setDescription(bool $description = true): Column { $this->description = $description; return $this; }

  public function getHidden(): bool { return $this->hidden; }
  public function setHidden(bool $hidden = true): Column { $this->hidden = $hidden; return $this; }

  public function getRawSqlDefinition(): string { return $this->rawSqlDefinition; }
  public function setRawSqlDefinition(string $rawSqlDefinition): Column { $this->rawSqlDefinition = $rawSqlDefinition; return $this; }

  public function getInputComponent(): string { return $this->inputComponent; }
  public function setInputComponent(string $inputComponent): Column { $this->inputComponent = $inputComponent; return $this; }

  public function getDefaultValue(): mixed { return $this->defaultValue; }
  public function setDefaultValue(mixed $defaultValue): Column { $this->defaultValue = $defaultValue; return $this; }


  public function getProperty(string $name): ColumnProperty
  {
    return $this->properties[$name];
  }

  public function setProperty(string $propertyName, ColumnProperty $property): Column
  {
    $property->setColumn($this);
    $this->properties[$propertyName] = $property;
    return $this;
  }

  public function jsonSerialize(): array
  {
    $column = [
      'type' => $this->type,
      'title' => $this->title,
      'readonly' => $this->readonly,
      'required' => $this->required,
      'inputJSX' => $this->inputComponent,
      'defaultValue' => $this->defaultValue,
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

  public function getNullValue(): mixed
  {
    return null;
  }
  
  public function normalize(mixed $value): mixed
  {
    return $value;
  }

  public function validate(mixed $value): bool
  {
    return TRUE;
  }

  public function sqlCreateString(string $table, string $columnName): string
  {
    return (empty($this->sqlDataType) ? '' : "`{$columnName}` {$this->sqlDataType} " . $this->getRawSqlDefinition());
  }

  public function sqlIndexString(string $table, string $columnName): string { return ''; }

}