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

  public function describeColumns(): array
  {
    return array_merge(parent::describeColumns(), [
      'id_user' => new \ADIOS\Core\Db\Column\Lookup($this, 'User', User::class),
      'id_role' => new \ADIOS\Core\Db\Column\Lookup($this, 'Role', UserRole::class),
    ]);
  }
}
