<?php

namespace ADIOS\Models;

class UserHasRole extends \ADIOS\Core\Model {

  public string $table = "user_has_roles";
  public string $recordManagerClass = RecordManagers\UserHasRole::class;
  public bool $isJunctionTable = FALSE;

  public function describeColumns(): array
  {
    return array_merge(parent::describeColumns(), [
      'id_user' => new \ADIOS\Core\Db\Column\Lookup($this, 'User', User::class),
      'id_role' => new \ADIOS\Core\Db\Column\Lookup($this, 'Role', UserRole::class),
    ]);
  }
}
