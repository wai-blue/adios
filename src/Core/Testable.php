<?php

namespace ADIOS\Core;

interface Testable {
  public function assert(string $assertionName, bool $assertion);
}