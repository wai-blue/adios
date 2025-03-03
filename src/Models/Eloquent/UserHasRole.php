<?php

namespace ADIOS\Models\Eloquent;

use \Illuminate\Database\Eloquent\Relations\HasMany;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserHasRole extends \ADIOS\Core\ModelEloquent {
  public static $snakeAttributes = false;
  public $table = 'user_has_roles';

}
