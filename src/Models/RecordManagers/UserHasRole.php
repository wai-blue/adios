<?php

namespace ADIOS\Models\RecordManagers;

use \Illuminate\Database\Eloquent\Relations\HasMany;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserHasRole extends \ADIOS\Core\EloquentRecordManager {
  public static $snakeAttributes = false;
  public $table = 'user_has_roles';

}
