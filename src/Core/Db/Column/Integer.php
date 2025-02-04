<?php

namespace ADIOS\Core\Db\Column;

class Integer extends \ADIOS\Core\Db\Column
{

  protected string $type = 'int';
  protected int $byteSize = 255;
  protected array $enumValues = [];

  public function __construct(\ADIOS\Core\Model $model, string $title, int $byteSize = 255)
  {
    parent::__construct($model, $title);
    $this->byteSize = $byteSize;
  }

  public function getByteSize(): int { return $this->byteSize; }
  public function setByteSize(int $byteSize): Decimal { $this->byteSize = $byteSize; return $this; }

  public function getEnumValues(): array { return $this->enumValues; }
  public function setEnumValues(array $enumValues): \ADIOS\Core\Db\Column\Integer { $this->enumValues = $enumValues; return $this; }

  public function describeInput(): \ADIOS\Core\Description\Input
  {
    $description = parent::describeInput();
    if (!empty($this->getEnumValues())) $description->setEnumValues($this->getEnumValues());
    return $description;
  }

  public function jsonSerialize(): array
  {
    $column = parent::jsonSerialize();
    $column['byteSize'] = $this->byteSize;
    if (count($this->enumValues) != 0) {
      $column['enumValues'] = $this->enumValues;
    }
    return $column;
  }

  public function getNullValue(): mixed
  {
    return 0;
  }
  
  public function normalize(mixed $value): mixed
  {
    return (int) $value;
  }

  public function validate(mixed $value): bool
  {
    return empty($value) || is_numeric($value);
  }

  public function sqlCreateString(string $table, string $columnName): string
  {
    return "`{$columnName}` int($this->byteSize) " . $this->getRawSqlDefinition();
  }

  public function sqlIndexString(string $table, string $columnName): string
  {
    return "index `{$columnName}` (`{$columnName}`)";
  }

}