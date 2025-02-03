<?php

namespace ADIOS\Core\Db\ColumnProperty;

class Autocomplete extends \ADIOS\Core\Db\ColumnProperty
{

  protected string $endpoint = '';
  protected bool $creatable = false;

  public function __construct(string $endpoint = '') {
    parent::__construct($endpoint);
    $this->endpoint = $endpoint;
  }

  public function getEndpoint(): string { return $this->endpoint; }
  public function setEndpoint(string $endpoint): Autocomplete { $this->endpoint = $endpoint; return $this; }

  public function getCreatable(): bool { return $this->creatable; }
  public function setCreatable(bool $creatable = true): Autocomplete { $this->creatable = $creatable; return $this; }

  public function jsonSerialize(): array
  {
    return [
      'endpoint' => $this->endpoint,
      'creatable' => $this->creatable,
    ];
  }

}