<?php

namespace ADIOS\Models;

class UserHasRole extends \ADIOS\Core\Model {
  public string $eloquentClass = Eloquent\UserHasRole::class;

  public bool $isJunctionTable = FALSE;

  public function __construct(\ADIOS\Core\Loader $app)
  {
    $this->sqlName = "user_has_roles";
    parent::__construct($app);
  }

  public function columns(array $columns = []): array
  {
    return parent::columns([
      'id_user' => (new \ADIOS\Core\Db\Column\Lookup($this, 'User', \ADIOS\Models\User::class)),
      'id_role' => (new \ADIOS\Core\Db\Column\Lookup($this, 'Role', \ADIOS\Models\UserRole::class)),
    ]);
  }
}
