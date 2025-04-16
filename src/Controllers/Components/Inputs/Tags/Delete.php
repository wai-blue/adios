<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Controllers\Components\Inputs\Tags;

use Illuminate\Database\QueryException;

/**
 * @package Components\Controllers\Tags
 */
class Delete extends \ADIOS\Core\Controller {
  public bool $hideDefaultDesktop = true;

  function __construct(\ADIOS\Core\Loader $app, array $params = []) {
    parent::__construct($app, $params);
  }

  public function renderJson(): ?array { 
    try {
      $id = $this->app->urlParamAsInteger('id');
      $model = $this->app->urlParamAsString('model');
      $junction = $this->app->urlParamAsString('junction');

      // Validate required params
      if ($model == '') throw new \Exception("Unknown model");
      if ($junction == '') throw new \Exception("Unknown junction model");
      if ($id == 0) throw new \Exception("Unknown id");

      $tmpModel = $this->app->getModel($model);
      $junctionData = $tmpModel->junctions[$junction] ?? null;

      if ($junctionData == null) {
        throw new \Exception("Junction {$junction} in {$model} not found");
      }

      $junctionModel = $this->app->getModel($junctionData['junctionModel']);
      $junctionOptionKeyColumn = $junctionModel->getColumns()[$junctionData['optionKeyColumn']]->toArray();
      $junctionOptionKeyModel = $this->app->getModel($junctionOptionKeyColumn['model']);

      $junctionItemsToDelete = $junctionModel->record->where($junctionData['optionKeyColumn'], $id)
        ->get();

      foreach ($junctionItemsToDelete as $junctionItem) {
        $junctionModel->record->find($junctionItem->id)->recordDelete();
      }

      $junctionOptionKeyModel->record->find($id)->recordDelete();

      return [];
    } catch (QueryException $e) {
      http_response_code(500);

      return [
        'status' => 'error',
        'message' => $e->getMessage() 
      ];
    } catch (\Exception $e) {
      http_response_code(400);

      return [
        'status' => 'error',
        'message' => $e->getMessage() 
      ];
    }
  }

}
