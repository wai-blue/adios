<?php

namespace ADIOS\Core;

class Translator {
  public \ADIOS\Core\Loader $app;

  public string $dictionaryFilename = "Core-Loader";
  public array $dictionary = [];

  public function __construct(\ADIOS\Core\Loader $app)
  {
    $this->app = $app;
  }

  public function getDictionaryFilename(string $context, string $language = ''): string
  {
    $dictionaryFile = '';

    if (empty($language)) $language = $this->app->configAsString('language', 'en');
    if (empty($language)) $language = 'en';

    if (strlen($language) == 2) {
      $dictionaryFile = $this->app->configAsString('srcDir') . "/Lang/{$language}.json";
    }

    return $dictionaryFile;
  }

  public function loadDictionary(string $language = ""): array
  {
    $dictionary = [];
    $dictionaryFile = $this->getDictionaryFilename($language);

    if (!empty($dictionaryFile) && file_exists($dictionaryFile)) {
      $dictionary = @json_decode(file_get_contents($dictionaryFile), true);
    }

    return $dictionary;
  }

  public function addToDictionary(string $string, string $context, string $toLanguage) {
    $dictionaryFile = $this->getDictionaryFilename($context, $toLanguage);
    $this->dictionary[$toLanguage][$context][$string] = '';

    if (is_file($dictionaryFile)) {
      file_put_contents(
        $dictionaryFile,
        json_encode(
          $this->dictionary[$toLanguage],
          JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        )
      );
    }
  }

  public function translate(string $string, array $vars = [], string $context = "app", string $toLanguage = ""): string
  {
    if (empty($toLanguage)) {
      $toLanguage = $this->app->configAsString('language', 'en');
    }

    if ($toLanguage == "en") {
      $translated = $string;
    } else {
      if (empty($this->dictionary[$toLanguage])) {
        $this->dictionary[$toLanguage] = $this->loadDictionary($toLanguage);
      }

      $dictionary = $this->dictionary[$toLanguage] ?? [];

      if (empty($dictionary[$context][$string]) && $toLanguage != 'en') {
        $translated = $string;
        $this->addToDictionary($string, $context, $toLanguage);
      } else {
        $translated = $dictionary[$context][$string];
      }
    }

    if (empty($translated)) $translated = $string;

    foreach ($vars as $varName => $varValue) {
      $translated = str_replace('{{ ' . $varName . ' }}', $varValue, $translated);
    }

    return $translated;
  }

}