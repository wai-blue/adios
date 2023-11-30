<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Controllers\Components\Form;

/**
 * @package Components\Controllers\Table
 */
class OnSave extends \ADIOS\Core\Controller {
  public static bool $hideDefaultDesktop = true;

  public function renderJson() { 
    try {
      $params = $this->getRequestParams();

      $tmpModel = $this->adios->getModel($params['model']);
      
      $tmpModel->recordSave($params['inputs']);

      return [
        'status' => 'success'
      ];
    } catch (\ADIOS\Core\Exceptions\RecordSaveException $e) {
      http_response_code(422);

      $invalidInputs = json_decode($e->getMessage());

      return [
        'status' => 'error',
        'message' => 'Invalid inputs',
        'invalidInputs' => $invalidInputs
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