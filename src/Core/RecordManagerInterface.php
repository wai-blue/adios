<?php

namespace ADIOS\Core;

/**
  * Record-management
  * CRUD-like layer for manipulating records (data)
*/

interface RecordManagerInterface {

  // public function getRelationsToRead(): array;
  // public function setRelationsToRead(array $relationsToRead): void;
  public function getMaxReadLevel(): array;
  public function setMaxReadLevel(array $maxReadLevel): void;

  /**
   * prepareReadQuery
   * @param mixed $query Leave empty for default behaviour.
   * @param int $level Leave empty for default behaviour.
   * @return mixed Object for reading records.
   */
  public function prepareReadQuery(mixed $query = null, int $level = 0): mixed;
  public function addFulltextSearchToQuery(mixed $query, string $fulltextSearch): mixed;
  public function addColumnSearchToQuery(mixed $query, array $columnSearch): mixed;
  public function addOrderByToQuery(mixed $query, array $orderBy): mixed;
  public function recordReadMany(mixed $query, int $itemsPerPage, int $page): array;
  public function recordRead(mixed $query): array;

  public function recordEncryptIds(array $record): array;
  public function recordDecryptIds(array $record): array;
  public function recordCreate(array $record): array;
  public function recordUpdate(array $record): array;
  public function recordDelete(int|string $id): int;
  public function recordSave(array $record, int $idMasterRecord = 0): array;

  /**
   * validate
   * @param array<string, mixed> $record
   * @return array<string, mixed>
   */
  public function recordValidate(array $record): array;
  public function recordNormalize(array $record): array;

}