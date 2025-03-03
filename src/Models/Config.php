<?php

namespace ADIOS\Models;

class Config extends \ADIOS\Core\Model
{

  public string $table = 'config';
  public string $eloquentClass = Eloquent\Config::class;

  public function describeColumns(): array
  {
    return array_merge(parent::describeColumns(), [
      'path' => new \ADIOS\Core\Db\Column\Varchar($this, 'Path'),
      'value' => new \ADIOS\Core\Db\Column\Text($this, 'Value'),
    ]);
  }

  public function indexes(array $indexes = []): array
  {
    return parent::indexes([
      "path" => [
        "type" => "unique",
        "columns" => [
          "path" => [
            "order" => "asc",
          ],
        ],
      ],
    ]);
  }

}
