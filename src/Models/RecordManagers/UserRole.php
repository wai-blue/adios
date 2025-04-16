<?php

namespace ADIOS\Models\RecordManagers;

use \Illuminate\Database\Eloquent\Relations\HasMany;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRole extends \ADIOS\Core\EloquentRecordManager {
  public static $snakeAttributes = false;
  public $table = 'user_roles';

}
