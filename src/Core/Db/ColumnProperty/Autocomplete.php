<?php

namespace ADIOS\Core\Db\ColumnProperty;

class Autocomplete extends \ADIOS\Core\Db\ColumnProperty
{

  protected string $endpoint = '';

  public function __constructor(\ADIOS\Core\Db $db)
  {
    $this->db = $db;
  }

  public function getEndpoint(): string
  {
    return $this->endpoint;
  }

  public function setEndpoint(string $endpoint): Autocomplete
  {
    $this->endpoint = $endpoint;
    return $this;
  }

  public function jsonSerialize(): array
  {
    return [
      'endpoint' => $this->endpoint,
    ];
  }

}