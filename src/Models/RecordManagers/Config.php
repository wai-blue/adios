<?php

namespace ADIOS\Models\RecordManagers;

use \Illuminate\Database\Eloquent\Relations\HasMany;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;

class Config extends \ADIOS\Core\EloquentRecordManager {
  public static $snakeAttributes = false;
  public $table = 'config';

}
