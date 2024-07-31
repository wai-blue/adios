<?php

namespace ADIOS\Models\Eloquent;

use \Illuminate\Database\Eloquent\Relations\HasMany;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;

class Token extends \Illuminate\Database\Eloquent\Model {
  public static $snakeAttributes = false;
  public $table = '_tokens';

}
