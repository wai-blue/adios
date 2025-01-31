<?php

namespace ADIOS\Models;

class Config extends \ADIOS\Core\Model {

  public string $eloquentClass = Eloquent\Config::class;

  public function __construct(\ADIOS\Core\Loader $app) {
    $this->sqlName = "config";
    parent::__construct($app);
  }

  public function columns(array $columns = []): array
  {
    return parent::columns([
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
