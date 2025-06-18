<?php

namespace ADIOS\Core\Description;


class Input implements \JsonSerializable
{

  protected string $type = '';
  protected string $title = '';
  protected bool $readonly = false;
  protected bool $required = false;
  protected string $placeholder = '';
  protected int $decimals = 4;
  protected string $unit = '';
  protected string $format = '';
  protected string $description = '';
  protected string $reactComponent = '';
  protected string $lookupModel = '';
  protected array $extendedProps = [];
  protected array $enumValues = [];
  protected array $enumCssClasses = [];
  protected array $predefinedValues = [];
  protected mixed $defaultValue = null;

  /** @var array<string, \ADIOS\Core\Description\InputProperty> */
  protected array $properties = [];

  public function getType(): string { return $this->type; }
  public function setType(string $type): Input { $this->type = $type; return $this; }

  public function getTitle(): string { return $this->title; }
  public function setTitle(string $title): Input { $this->title = $title; return $this; }

  public function getReactComponent(): string { return $this->reactComponent; }
  public function setReactComponent(string $reactComponent): Input { $this->reactComponent = $reactComponent; return $this; }

  public function getReadonly(): bool { return $this->readonly; }
  public function setReadonly(bool $readonly = true): Input { $this->readonly = $readonly; return $this; }

  public function getRequired(): bool { return $this->required; }
  public function setRequired(bool $required = true): Input { $this->required = $required; return $this; }

  public function getPlaceholder(): bool { return $this->placeholder; }
  public function setPlaceholder(bool $placeholder = true): Input { $this->placeholder = $placeholder; return $this; }

  public function getDecimals(): int { return $this->decimals; }
  public function setDecimals(int $decimals): Decimal { $this->decimals = $decimals; return $this; }

  public function getUnit(): string { return $this->unit; }
  public function setUnit(string $unit): Input { $this->unit = $unit; return $this; }

  public function getFormat(): bool { return $this->format; }
  public function setFormat(bool $format = true): Input { $this->format = $format; return $this; }

  public function getDescription(): string { return $this->description; }
  public function setDescription(string $description): Input { $this->description = $description; return $this; }

  public function getLookupModel(): string { return $this->lookupModel; }
  public function setLookupModel(string $lookupModel): Input { $this->lookupModel = $lookupModel; return $this; }

  public function getExtendedProps(): array { return $this->extendedProps; }
  public function setExtendedProps(array $extendedProps): Input { $this->extendedProps = $extendedProps; return $this; }

  public function getEnumValues(): array { return $this->enumValues; }
  public function setEnumValues(array $enumValues): Input { $this->enumValues = $enumValues; return $this; }

  public function getEnumCssClasses(): array { return $this->enumCssClasses; }
  public function setEnumCssClasses(array $enumCssClasses): Input { $this->enumCssClasses = $enumCssClasses; return $this; }

  public function getPredefinedValues(): array { return $this->predefinedValues; }
  public function setPredefinedValues(array $predefinedValues): Input { $this->predefinedValues = $predefinedValues; return $this; }

  public function getProperty(string $name): InputProperty { return $this->properties[$name]; }
  public function setProperty(string $propertyName, InputProperty $property): Input { $this->properties[$propertyName] = $property; return $this; }

  public function getDefaultValue(): mixed { return $this->defaultValue; }
  public function setDefaultValue(mixed $defaultValue): Input { $this->defaultValue = $defaultValue; return $this; }

  public function jsonSerialize(): array
  {
    $json = ['type' => $this->type];
    if (!empty($this->title)) $json['title'] = $this->title;
    if (!empty($this->reactComponent)) $json['reactComponent'] = $this->reactComponent;
    if (!empty($this->readonly)) $json['readonly'] = $this->readonly;
    if (!empty($this->required)) $json['required'] = $this->required;
    if (!empty($this->placeholder)) $json['placeholder'] = $this->placeholder;
    if (!empty($this->unit)) $json['unit'] = $this->unit;
    if (!empty($this->format)) $json['format'] = $this->format;
    if (!empty($this->description)) $json['description'] = $this->description;
    if (!empty($this->lookupModel)) $json['model'] = $this->lookupModel;
    if (!empty($this->extendedProps)) $json['extendedProps'] = $this->extendedProps;
    if (!empty($this->enumValues)) $json['enumValues'] = $this->enumValues;
    if (!empty($this->enumCssClasses)) $json['enumCssClasses'] = $this->enumCssClasses;
    if (!empty($this->predefinedValues)) $json['predefinedValues'] = $this->predefinedValues;
    if (!empty($this->defaultValue)) $json['defaultValue'] = $this->defaultValue;

    foreach ($this->properties as $name => $property) {
      $json[$name] = $property->jsonSerialize();
    }

    return $json;
  }

  public function toArray(): array
  {
    return $this->jsonSerialize();
  }

}