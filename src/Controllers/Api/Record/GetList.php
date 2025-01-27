<?php

namespace ADIOS\Controllers\Api\Record;

use Illuminate\Support\Str;

class GetList extends \ADIOS\Core\ApiController {
  public \ADIOS\Core\Model $model;
  protected ?\Illuminate\Database\Eloquent\Builder $query = null;
  public array $data = [];
  private int $itemsPerPage = 15;

  function __construct(\ADIOS\Core\Loader $app, array $params = []) {
    parent::__construct($app, $params);
    $this->permission = $this->app->params['model'] . ':Read';
    $this->model = $this->app->getModel($this->app->params['model']);
  }

  public function response(): array
  {
    return $this->model->recordGetList(
      $this->app->params['includeRelations'] ?? null,
      (int) ($this->app->params['maxRelationLevel'] ?? 2),
      (string) ($this->app->params['search'] ?? ''),
      (array) ($this->app->params['filterBy'] ?? []),
      (array) ($this->app->params['where'] ?? []),
      (array) ($this->app->params['orderBy'] ?? []),
      (int) $this->app->params['itemsPerPage'] ?? 15,
      $this->app->params['page'],
    );
  }
}
