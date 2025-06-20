<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core;

use ADIOS\Core\Db\DataType;
use ADIOS\Core\Db\Query;
use ADIOS\Core\Exceptions\DBException;
use ADIOS\Core\Exceptions\RecordSaveException;
use ADIOS\Core\ViewsWithController\Form;
use ADIOS\Core\ViewsWithController\Table;
use Closure;
use Exception;
use ReflectionClass;

/**
 * Core implementation of model.
 */
class Model
{
  const HAS_ONE = 'hasOne';
  const HAS_MANY = 'hasMany';
  const BELONGS_TO = 'belongsTo';

  /**
   * Full name of the model. Useful for getModel() function
   */
  public string $fullName = "";

  /**
   * Short name of the model. Useful for debugging purposes
   */
  public string $shortName = "";

  /**
   * Reference to ADIOS object
   *
   * @var mixed
   */
  public Loader $app;

  // public \ADIOS\Core\Record $record;
  public object $record;

  /**
   * SQL-compatible string used to render displayed value of the record when used
   * as a lookup.
   */
  public ?string $lookupSqlValue = NULL;

  public ?string $lookupUrlDetail = '';
  public ?string $lookupUrlAdd = '';

  /**
   * If set to TRUE, the SQL table will not contain the ID autoincrement column
   */
  public bool $isJunctionTable = FALSE;

  public string $translationContext = '';

  public string $sqlEngine = 'InnoDB';

  public string $table = '';
  public string $recordManagerClass = '';
  public array $relations = [];

  public ?array $junctions = [];

  /** @property array<string, \ADIOS\Core\Db\Column> */
  protected array $columns = [];

  /**
   * Creates instance of model's object.
   *
   * @param mixed $app
   * @return void
   */
  public function __construct(\ADIOS\Core\Loader $app)
  {
    $reflection = new \ReflectionClass($this);

    $this->app = $app;
    $this->columns = $this->describeColumns();

    $recordManagerClass = $this->recordManagerClass;
    if (!empty($recordManagerClass) && $this->isDatabaseConnected()) {
      $this->record = $this->initRecordManager();
      $this->record->model = $this;
      $this->record->app = $this->app;
    }

    $this->fullName = str_replace("\\", "/", $reflection->getName());

    if (empty($this->translationContext)) {
      $this->translationContext = trim(str_replace('/', '\\', $this->fullName), '\\');
    }

    $tmp = explode("/", $this->fullName);
    $this->shortName = end($tmp);


    $currentVersion = (int)$this->getCurrentInstalledVersion();
    $lastVersion = $this->getLastAvailableVersion();

  }

  public function initRecordManager(): null|object
  {
    $recordManagerClass = $this->recordManagerClass;
    $recordManager = new $recordManagerClass();
    $recordManager->model = $this;
    return $recordManager;
  }

  public function isDatabaseConnected(): bool
  {
    return $this->app->pdo->isConnected;
  }

  /**
   * Retrieves value of configuration parameter.
   *
   * @return void
   */
  public function getConfig(string $configName): string
  {
    return $this->app->config->getAsString('models/' . str_replace("/", "-", $this->fullName) . '/' . $configName);
  }

  /**
   * Shorthand for ADIOS core translate() function. Uses own language dictionary.
   *
   * @param string $string String to be translated
   * @param string $context Context where the string is used
   * @param string $toLanguage Output language
   * @return string Translated string.
   */
  public function translate(string $string, array $vars = []): string
  {
    return $this->app->translate($string, $vars, $this->translationContext);
  }

  /**
   * Checks whether model is installed.
   *
   * @return bool TRUE if model is installed, otherwise FALSE.
   */
  public function isInstalled(): bool
  {
    return $this->getConfig('installed-version') != "";
  }

  /**
   * Gets the current installed version of the model. Used during installing upgrades.
   *
   * @return void
   */
  private function getCurrentInstalledVersion(): int
  {
    return (int)($this->getConfig('installed-version') ?? 0);
  }

  private function getLastAvailableVersion(): int
  {
    return max(array_keys($this->upgrades()));
  }

  /**
   * Returns list of available upgrades. This method must be overriden by each model.
   *
   * @return array List of available upgrades. Keys of the array are simple numbers starting from 1.
   */
  public function upgrades(): array
  {
    return [
      0 => [], // upgrade to version 0 is the same as installation
    ];
  }

  public function getSqlCreateTableCommands(): array
  {

    $columns = $this->columns;

    $createSql = "create table `{$this->table}` (\n";

    foreach ($columns as $columnName => $column) {
      $tmp = $column->sqlCreateString($this->table, $columnName);
      if (!empty($tmp)) $createSql .= " {$tmp},\n";
    }

    // indexy
    foreach ($columns as $columnName => $column) {
      $tmp = $column->sqlIndexString($this->table, $columnName);
      if (!empty($tmp)) $createSql .= " {$tmp},\n";
    }

    $createSql = substr($createSql, 0, -2) . ") ENGINE = {$this->sqlEngine}";

    $commands = [];
    $commands[] = "SET foreign_key_checks = 0";
    $commands[] = "drop table if exists `{$this->table}`";
    $commands[] = $createSql;
    $commands[] = "SET foreign_key_checks = 1";

    return $commands;

  }

  public function createSqlTable()
  {

    $this->app->pdo->startTransaction();
    foreach ($this->getSqlCreateTableCommands() as $command) {
      $this->app->pdo->execute($command);
    }
    $this->app->pdo->commit();
  }

  /**
   * Installs the first version of the model into SQL database. Automatically creates indexes.
   *
   * @return void
   */
  public function install()
  {
    if (!empty($this->table)) {
      $this->createSqlTable();

      foreach ($this->indexes() as $indexOrConstraintName => $indexDef) {
        if (empty($indexOrConstraintName) || is_numeric($indexOrConstraintName)) {
          $indexOrConstraintName = md5(json_encode($indexDef) . uniqid());
        }

        $tmpColumns = "";

        foreach ($indexDef['columns'] as $tmpKey => $tmpValue) {
          if (!is_numeric($tmpKey)) {
            // v tomto pripade je nazov stlpca v kluci a vo value mozu byt dalsie nastavenia
            $tmpColumnName = $tmpKey;
            $tmpOrder = strtolower($tmpValue['order'] ?? 'asc');
            if (!in_array($tmpOrder, ['asc', 'desc'])) {
              $tmpOrder = 'asc';
            }
          } else {
            $tmpColumnName = $tmpValue;
            $tmpOrder = '';
          }

          $tmpColumns .=
            ($tmpColumns == '' ? '' : ', ')
            . '`' . $tmpColumnName . '`'
            . (empty($tmpOrder) ? '' : ' ' . $tmpOrder);
        }

        switch ($indexDef["type"]) {
          case "index":
            $this->app->pdo->execute("
              alter table `" . $this->table . "`
              add index `{$indexOrConstraintName}` ({$tmpColumns})
            ");
            break;
          case "unique":
            $this->app->pdo->execute("
              alter table `" . $this->table . "`
              add constraint `{$indexOrConstraintName}` unique ({$tmpColumns})
            ");
            break;
        }
      }

      $this->createSqlForeignKeys();

      $this->app->config->save(
        'models/' . str_replace("/", "-", $this->fullName) . '/installed-version',
        max(array_keys($this->upgrades()))
      );

      return TRUE;
    } else {
      return FALSE;
    }
  }

  public function hasAvailableUpgrades(): bool
  {
    $currentVersion = $this->getCurrentInstalledVersion();
    $lastVersion = $this->getLastAvailableVersion();
    return ($lastVersion > $currentVersion);
  }

  /**
   * Installs all upgrades of the model. Internaly stores current version and
   * compares it to list of available upgrades.
   *
   * @return void
   * @throws DBException When an error occured during the upgrade.
   */
  public function installUpgrades(): void
  {
    if ($this->hasAvailableUpgrades()) {
      $currentVersion = (int)$this->getCurrentInstalledVersion();
      $lastVersion = $this->getLastAvailableVersion();

      try {
        $this->app->pdo->startTransaction();

        $upgrades = $this->upgrades();

        for ($v = $currentVersion + 1; $v <= $lastVersion; $v++) {
          if (is_array($upgrades[$v])) {
            foreach ($upgrades[$v] as $query) {
              $this->app->pdo->execute($query);
            }
          }
        }

        $this->app->pdo->commit();
        $this->saveConfig('installed-version', $lastVersion);
      } catch (DBException $e) {
        $this->app->pdo->rollback();
        throw new DBException($e->getMessage());
      }
    }
  }

  public function dropTableIfExists(): \ADIOS\Core\Model
  {
    $this->app->pdo->execute("set foreign_key_checks = 0");
    $this->app->pdo->execute("drop table if exists `" . $this->table . "`");
    $this->app->pdo->execute("set foreign_key_checks = 1");
    return $this;
  }

  /**
   * Create foreign keys for the SQL table. Called when all models are installed.
   *
   * @return void
   */
  public function createSqlForeignKeys()
  {

    $sql = '';
    foreach ($this->getColumns() as $colName => $column) {
      $columnDefinition = $column->toArray();

      if (
        !($columnDefinition['disableForeignKey'] ?? false)
        && 'lookup' == $columnDefinition['type']
      ) {
        $lookupModel = $this->app->getModel($columnDefinition['model']);
        $foreignKeyColumn = $columnDefinition['foreignKeyColumn'] ?? "id";
        $foreignKeyOnDelete = $columnDefinition['foreignKeyOnDelete'] ?? "RESTRICT";
        $foreignKeyOnUpdate = $columnDefinition['foreignKeyOnUpdate'] ?? "RESTRICT";

        $sql .= "
          ALTER TABLE `{$this->table}`
          ADD CONSTRAINT `fk_" . md5($this->table . '_' . $colName) . "`
          FOREIGN KEY (`{$colName}`)
          REFERENCES `" . $lookupModel->getFullTableSqlName() . "` (`{$foreignKeyColumn}`)
          ON DELETE {$foreignKeyOnDelete}
          ON UPDATE {$foreignKeyOnUpdate};;
        ";
      }
    }

    if (!empty($sql)) {
      foreach (explode(';;', $sql) as $query) {
        $this->app->pdo->execute(trim($query));
      }
    }

  }

  /**
   * Returns full name of the model's SQL table
   *
   * @return string Full name of the model's SQL table
   */
  public function getFullTableSqlName()
  {
    return $this->table;
  }

  public function getLookupSqlValue(string $tableAlias = ''): string
  {
    $value = $this->lookupSqlValue ?? "concat('{$this->fullName}, id = ', {%TABLE%}.id)";

    return ($tableAlias !== ''
      ? str_replace('{%TABLE%}', "`{$tableAlias}`", $value)
      : $value
    );
  }

  //////////////////////////////////////////////////////////////////
  // definition of columns

  public function hasColumn(string $column): bool
  {
    return in_array($column, array_keys($this->getColumns()));
  }

  /** @return array<string, \ADIOS\Core\Db\Column> */
  public function describeColumns(): array
  {
    $columns = [];

    if (!$this->isJunctionTable) {
      $columns['id'] = new \ADIOS\Core\Db\Column\PrimaryKey($this, 'ID', 8);
    }

    return $columns;
  }

  public function getColumns(): array
  {
    return $this->columns;
  }

  public function getColumn(string $column): Db\Column
  {
    return $this->columns[$column];
  }

  /**
   * columnNames
   * @return array<string>
   */
  public function columnNames(): array
  {
    return array_keys($this->columns);
  }

  /**
   * indexes
   * @param array<string, mixed> $indexes
   * @return array<string, mixed>
   */
  public function indexes(array $indexes = []): array
  {
    return $indexes;
  }

  /**
   * indexNames
   * @return array<string>
   */
  public function indexNames(): array
  {
    return array_keys($this->indexNames());
  }

  //////////////////////////////////////////////////////////////////
  // CRUD methods

  public function getById(int $id)
  {
    $item = $this->recordGet(function($q) use ($id) { $q->where($this->table . '.id', $id); });
    return $item;
  }

  //////////////////////////////////////////////////////////////////
  // Description API

  public function describeInput(string $columnName): \ADIOS\Core\Description\Input
  {
    return $this->columns[$columnName]->describeInput();
  }

  public function describeTable(): \ADIOS\Core\Description\Table
  {
    $columns = $this->columns;
    if (isset($columns['id'])) unset($columns['id']);

    $description = new \ADIOS\Core\Description\Table();
    foreach ($columns as $columnName => $column) {
      if (!$column->getHidden()) {
        $description->columns[$columnName] = $column;
      }
    }

    $description->inputs = [];
    foreach ($columns as $columnName => $column) {
      if ($columnName == 'id') continue;
      $description->inputs[$columnName] = $this->describeInput($columnName);
    }

    $description->permissions = [
      'canRead' => $this->app->permissions->granted($this->fullName . ':Read'),
      'canCreate' => $this->app->permissions->granted($this->fullName . ':Create'),
      'canUpdate' => $this->app->permissions->granted($this->fullName . ':Update'),
      'canDelete' => $this->app->permissions->granted($this->fullName . ':Delete'),
    ];

    return $description;
  }

  public function describeForm(): \ADIOS\Core\Description\Form
  {
    $description = new \ADIOS\Core\Description\Form();

    $columnNames = $this->columnNames();

    $description->inputs = [];
    foreach ($columnNames as $columnName) {
      if ($columnName == 'id') continue;
      $inputDesc = $this->describeInput($columnName);
      $description->inputs[$columnName] = $inputDesc;
      if ($inputDesc->getDefaultValue() !== null) {
        $description->defaultValues[$columnName] = $inputDesc->getDefaultValue();
      }
    }

    $description->permissions = [
      'canRead' => $this->app->permissions->granted($this->fullName . ':Read'),
      'canCreate' => $this->app->permissions->granted($this->fullName . ':Create'),
      'canUpdate' => $this->app->permissions->granted($this->fullName . ':Update'),
      'canDelete' => $this->app->permissions->granted($this->fullName . ':Delete'),
    ];

    $description->includeRelations = array_keys($this->relations);

    return $description;
  }

  //////////////////////////////////////////////////////////////////
  // Record-related methods

  /**
   * recordGet
   */
  public function recordGet(callable|null $queryModifierCallback = null): array
  {
    $query = $this->record->prepareReadQuery();
    if ($queryModifierCallback !== null) $queryModifierCallback($query);
    $record = $this->record->recordRead($query);
    $record = $this->onAfterLoadRecord($record);
    return $record;
  }

  /**
   * recordGetList
   */
  public function recordGetList(
    string $fulltextSearch = '',
    array $columnSearch = [],
    array $orderBy = [],
    int $itemsPerPage = 15,
    int $page = 0,
  ): array
  {
    $query = $this->record->prepareReadQuery();
    $query = $this->record->addFulltextSearchToQuery($query, $fulltextSearch);
    $query = $this->record->addColumnSearchToQuery($query, $columnSearch);
    $query = $this->record->addOrderByToQuery($query, $orderBy);
    $paginatedRecords = $this->record->recordReadMany($query, $itemsPerPage, $page);

    foreach ($paginatedRecords['data'] as $key => $record) {
      $paginatedRecords['data'][$key] = $this->onAfterLoadRecord($record);
    }

    $paginatedRecords = $this->onAfterLoadRecords($paginatedRecords);

    return $paginatedRecords;
  }

  //////////////////////////////////////////////////////////////////
  // callbacks

  /**
   * onBeforeCreate
   * @param array<string, mixed> $record
   * @return array<string, mixed>
   */
  public function onBeforeCreate(array $record): array
  {
    return $record;
  }

  /**
   * onBeforeUpdate
   * @param array<string, mixed> $record
   * @return array<string, mixed>
   */
  public function onBeforeUpdate(array $record): array
  {
    return $record;
  }

  /**
   * onAfterCreate
   * @param array<string, mixed> $originalRecord
   * @param array<string, mixed> $savedRecord
   * @return array<string, mixed>
   */
  public function onAfterCreate(array $savedRecord): array
  {
    return $savedRecord;
  }

  /**
   * onAfterUpdate
   * @param array<string, mixed> $originalRecord
   * @param array<string, mixed> $savedRecord
   * @return array<string, mixed>
   */
  public function onAfterUpdate(array $originalRecord, array $savedRecord): array
  {
    return $savedRecord;
  }

  /**
   * onBeforeDelete
   * @param int $id
   * @return int
   */
  public function onBeforeDelete(int $id): int
  {
    return $id;
  }

  /**
   * onAfterDelete
   * @param int $id
   * @return int
   */
  public function onAfterDelete(int $id): int
  {
    return $id;
  }

  /**
   * onAfterLoadRecord
   * @param array<string, mixed> $record
   * @return array<string, mixed>
   */
  public function onAfterLoadRecord(array $record): array
  {
    return $record;
  }

  public function onAfterLoadRecords(array $records): array
  {
    return $records;
  }

}
