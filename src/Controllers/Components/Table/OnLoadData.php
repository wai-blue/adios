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
      
      $tmpModel = $this->adios->getModel($this->params['model']);

      $tableTitle = $tmpModel->tableTitle;
      $tmpColumns = $tmpModel->columns();

      $pageLength = (int) $params['pageLength'] ?? 15;

      $columns = [];
      foreach ($tmpColumns as $columnName => $column) {
        $columns[] = [
          'field' => $columnName,
          'headerName' => $column['title'],
          'flex' => 1,
          'type' => $column['type']
          //'html' => "<b class='text-danger'>xxx</b>"
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
        'title' => $tableTitle
      ];
    } catch (\ADIOS\Core\Exceptions\GeneralException $e) {
      // TODO: Error
    }
  }

}
