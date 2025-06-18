<?php

namespace ADIOS\Core\Db\Column;

class Password extends \ADIOS\Core\Db\Column\Varchar
{

  protected string $type = 'password';
  protected bool $hidden = true;

  public function normalize(mixed $value): mixed
  {
    if (is_array($value)) {
      if (method_exists($this->model, 'hashPassword')) {
        return $this->model->hashPassword((string) $value[0]);
      } else {
        return password_hash($value[0], PASSWORD_DEFAULT);
      }
    } else {
      return $value;
    }
  }

  public function validate($value): bool
  {
    if (is_array($value)) {
      return $value[0] == $value[1];
    } else {
      return true;
    }
  }

}