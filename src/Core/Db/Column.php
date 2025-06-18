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
  protected string $placeholder = '';
  protected string $unit = '';
  protected string $format = '';
  protected string $description = '';
  protected mixed $defaultValue = null;
  protected array $enumValues = [];
  protected array $enumCssClasses = [];
  protected array $predefinedValues = [];
  protected string $colorScale = '';
  protected string $cssClass = '';
  protected string $tableCellRenderer = '';

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

  public function getPlaceholder(): bool { return $this->placeholder; }
  public function setPlaceholder(bool $placeholder = true): Column { $this->placeholder = $placeholder; return $this; }

  public function getUnit(): string { return $this->unit; }
  public function setUnit(string $unit): Column { $this->unit = $unit; return $this; }

  public function getColorScale(): string { return $this->colorScale; }
  public function setColorScale(string $colorScale): Column { $this->colorScale = $colorScale; return $this; }

  public function getCssClass(): string { return $this->cssClass; }
  public function setCssClass(string $cssClass): Column { $this->cssClass = $cssClass; return $this; }

  public function getFormat(): bool { return $this->format; }
  public function setFormat(bool $format = true): Column { $this->format = $format; return $this; }

  public function getDescription(): string { return $this->description; }
  public function setDescription(string $description): Column { $this->description = $description; return $this; }

  public function getEnumValues(): array { return $this->enumValues; }
  public function setEnumValues(array $enumValues): Column { $this->enumValues = $enumValues; return $this; }

  public function getEnumCssClasses(): array { return $this->enumCssClasses; }
  public function setEnumCssClasses(array $enumCssClasses): Column { $this->enumCssClasses = $enumCssClasses; return $this; }

  public function getPredefinedValues(): array { return $this->predefinedValues; }
  public function setPredefinedValues(array $predefinedValues): Column { $this->predefinedValues = $predefinedValues; return $this; }

  public function getHidden(): bool { return $this->hidden; }
  public function setHidden(bool $hidden = true): Column { $this->hidden = $hidden; return $this; }

  public function getRawSqlDefinition(): string { return $this->rawSqlDefinition; }
  public function setRawSqlDefinition(string $rawSqlDefinition): Column { $this->rawSqlDefinition = $rawSqlDefinition; return $this; }

  public function getDefaultValue(): mixed { return $this->defaultValue; }
  public function setDefaultValue(mixed $defaultValue): Column { $this->defaultValue = $defaultValue; return $this; }

  public function getTableCellRenderer(): string { return $this->tableCellRenderer; }
  public function setTableCellRenderer(string $tableCellRenderer): Column { $this->tableCellRenderer = $tableCellRenderer; return $this; }

  public function describeInput(): \ADIOS\Core\Description\Input
  {
    $description = new \ADIOS\Core\Description\Input();
    $description->setType($this->getType());
    if (!empty($this->getTitle())) $description->setTitle($this->getTitle());
    if (!empty($this->getPlaceholder())) $description->setPlaceholder($this->getPlaceholder());
    if (!empty($this->getReadonly())) $description->setReadonly($this->getReadonly());
    if (!empty($this->getRequired())) $description->setRequired($this->getRequired());
    if (!empty($this->getDescription())) $description->setDescription($this->getDescription());
    if (!empty($this->getUnit())) $description->setUnit($this->getUnit());
    if (!empty($this->getFormat())) $description->setFormat($this->getFormat());
    if (!empty($this->getTableCellRenderer())) $description->setTableCellRenderer($this->getTableCellRenderer());
    if ($this->defaultValue !== null) $description->setDefaultValue($this->defaultValue);
    $description->setEnumValues($this->enumValues);
    $description->setEnumCssClasses($this->enumCssClasses);
    $description->setPredefinedValues($this->predefinedValues);
    return $description;
  }

  public function jsonSerialize(): array
  {
    $column = [
      'type' => $this->type,
      'title' => $this->title,
      'readonly' => $this->readonly,
      'required' => $this->required,
      'defaultValue' => $this->defaultValue,
      'unit' => $this->unit,
      'description' => $this->description,
      'format' => $this->format,
      'placeholder' => $this->placeholder,
      'colorScale' => $this->colorScale,
      'cssClass' => $this->cssClass,
      'tableCellRenderer' => $this->tableCellRenderer,
    ];

    if (count($this->enumValues) > 0) $column['enumValues'] = $this->enumValues;
    if (count($this->enumCssClasses) > 0) $column['enumCssClasses'] = $this->enumCssClasses;
    if (count($this->predefinedValues) > 0) $column['predefinedValues'] = $this->predefinedValues;

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