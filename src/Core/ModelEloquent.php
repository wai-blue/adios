<?php

namespace ADIOS\Core;

class ModelEloquent extends \Illuminate\Database\Eloquent\Model {
  protected $primaryKey = 'id';
  protected $guarded = [];
  public $timestamps = false;
  public static $snakeAttributes = false;

  public function __construct(array $attributes = [])
  {
    parent::__construct($attributes);
  }

  public function prepareReadQuery(): mixed
  {
    return $this;
  }

}
