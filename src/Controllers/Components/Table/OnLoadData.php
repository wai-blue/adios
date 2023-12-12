<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Controllers\Components\Table;

/**
 * @package Components\Controllers\Table
 */
class OnLoadData extends \ADIOS\Core\Controller {
  public static bool $hideDefaultDesktop = true;

  public function renderJson() { 
    try {
      $params = $this->params;
      
      $pageLength = (int) $params['pageLength'] ?? 15;

      $tmpModel = $this->adios->getModel($this->params['model']);

      $tmpColumns = $tmpModel->columns();

      $columns = [];
      foreach ($tmpColumns as $columnName => $column) {
        $columns[] = [
          'field' => $columnName,
          'headerName' => $column['title']
        ];
      }

      // FILTER BY
      if (isset($params['filterBy'])) {
        // TODO
      }

      // Search
      if (isset($params['search'])) {
        $tmpModel = $tmpModel->where(function ($query) use ($params, $tmpColumns) {
          foreach ($tmpColumns as $columnName => $column) {
            $query->orWhere($columnName, 'like', "%{$params['search']}%");
          }
        });
      }

      // ORDER BY
      if (isset($params['orderBy'])) {
        $tmpModel = $tmpModel->orderBy(
          $params['orderBy']['field'],
          $params['orderBy']['sort']);
      }

      // Laravel pagination
      $data = $tmpModel->paginate(
        $pageLength, ['*'], 
        'page', 
        $this->params['page']);

      return [
        'columns' => $columns, 
        'data' => $data,
        'title' => $tmpModel->tableTitle
      ];
    } catch (\ADIOS\Core\Exceptions\GeneralException $e) {
      // TODO: Error
    }
  }

}
