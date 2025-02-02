<?php

namespace ADIOS\Core\Db\Column;

class File extends \ADIOS\Core\Db\Column
{

  protected string $type = 'file';
  protected string $sqlDataType = 'varchar(255)';
  protected string $folderPath = '';
  protected string $renamePattern = '';

  public function getFolderPath(): string { return $this->folderPath; }
  public function setFolderPath(string $folderPath): Lookup { $this->folderPath = $folderPath; return $this; }

  public function getRenamePattern(): string { return $this->renamePattern; }
  public function setRenamePattern(string $renamePattern): Lookup { $this->renamePattern = $renamePattern; return $this; }

  public function normalize(mixed $value): mixed
  {
    if (!is_array($value) || empty($value['fileData']) || empty($value['fileName'])) return $value;

    $fileName = $value['fileName'];
    $fileData = preg_replace('/data:.*?,/', '', $value['fileData']);
    $fileData = @base64_decode($fileData);
    $folderPath = $this->getFolderPath();
    $title = $this->getTitle();
    $renamePattern = $this->getRenamePattern();

    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (empty($this->model->app->configAsString('uploadDir'))) throw new \Exception("{$title}: Upload folder is not configured.");
    if (!is_dir($this->model->app->configAsString('uploadDir'))) throw new \Exception("{$title}: Upload folder does not exist.");
    if (in_array($fileExtension, ['php', 'sh', 'exe', 'bat', 'htm', 'html', 'htaccess'])) {
      throw new \Exception("{$title}: This file type cannot be uploaded.");
    }

    if (strpos($folderPath, "..") !== false) throw new \Exception("{$title}: Invalid upload folder path.");

    if (empty($renamePattern)) {
      $tmpParts = pathinfo($fileName);
      $fileName = \ADIOS\Core\Helper::str2url($tmpParts['filename']) . '.' . $tmpParts['extension'];
    } else {
      $tmpParts = pathinfo($fileName);

      $fileName = $renamePattern;
      $fileName = str_replace("{%Y%}", date("Y"), $fileName);
      $fileName = str_replace("{%M%}", date("m"), $fileName);
      $fileName = str_replace("{%D%}", date("d"), $fileName);
      $fileName = str_replace("{%H%}", date("H"), $fileName);
      $fileName = str_replace("{%I%}", date("i"), $fileName);
      $fileName = str_replace("{%S%}", date("s"), $fileName);
      $fileName = str_replace("{%TS%}", strtotime("now"), $fileName);
      $fileName = str_replace("{%RAND%}", rand(1000, 9999), $fileName);
      $fileName = str_replace("{%BASENAME%}", $tmpParts['basename'], $fileName);
      $fileName = str_replace("{%BASENAME_ASCII%}", \ADIOS\Core\Helper::str2url($tmpParts['basename']), $fileName);
      $fileName = str_replace("{%FILENAME%}", $tmpParts['filename'], $fileName);
      $fileName = str_replace("{%FILENAME_ASCII%}", \ADIOS\Core\Helper::str2url($tmpParts['filename']), $fileName);
      $fileName = str_replace("{%EXT%}", $tmpParts['extension'], $fileName);
    }


    if (empty($folderPath)) $folderPath = ".";

    $uploadDir = $this->model->app->configAsString('uploadDir');

    if (!is_dir("{$uploadDir}/{$folderPath}")) {
      mkdir("{$uploadDir}/{$folderPath}", 0775, TRUE);
    }

    $fileNameNoVersion = $fileName;

    $destinationFileNoVersion = "{$uploadDir}/{$folderPath}/{$fileName}";
    $destinationFile = $destinationFileNoVersion;

    $verCnt = 1;
    while (is_file($destinationFile)) {
      $tmpParts = pathinfo($destinationFileNoVersion);
      $destinationFile = $tmpParts['dirname'] . '/' . $tmpParts['filename'] . ' (' . $verCnt .').' . $tmpParts['extension'];

      $tmpParts = pathinfo($fileNameNoVersion);
      $fileName = $tmpParts['filename'] . ' (' . $verCnt .').' . $tmpParts['extension'];

      $verCnt++;
    }

    \file_put_contents($destinationFile, $fileData);

    return "{$folderPath}/{$fileName}";
  }
}