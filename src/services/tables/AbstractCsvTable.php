<?php

namespace lenz\craft\essentials\services\tables;

use Craft;
use craft\helpers\Json;
use Throwable;

/**
 * Class AbstractCsvTable
 */
abstract class AbstractCsvTable extends AbstractTable
{
  /**
   * @var string
   */
  public $delimiter = ",";

  /**
   * @var string
   */
  public $enclosure = '"';

  /**
   * @var string
   */
  public $escape = "\\";

  /**
   * @var bool
   */
  public $hasHeaderRow = true;

  /**
   * @var int
   */
  public $length = 0;


  /**
   * @return string
   */
  abstract function getFileName(): string;


  // Protected methods
  // -----------------

  /**
   * @inheritDoc
   */
  protected function loadRows(): array {
    $fileName = Craft::parseEnv($this->getFileName());
    if (!file_exists($fileName)) {
      return [];
    }

    $handle = fopen($fileName, 'r');
    $columns = $this->getColumns();
    $header = $this->hasHeaderRow ? null : array_keys($columns);
    $rows = [];

    while (($data = fgetcsv($handle, $this->length, $this->delimiter, $this->enclosure, $this->escape)) !== false) {
      if (is_null($header)) {
        $header = $data;
        continue;
      }

      $attributes = [];
      foreach ($header as $index => $name) {
        $value = array_key_exists($index, $data) ? $data[$index] : '';
        $attributes[$name] = $columns[$name]->filter($value);
      }

      $rows[] = $this->createRow($attributes);
    }

    fclose($handle);
    return $rows;
  }

  /**
   * @inheritDoc
   */
  protected function saveRows(array $rows) {
    $fileName = Craft::parseEnv($this->getFileName());
    $handle = fopen($fileName, 'w');
    $header = array_keys($this->getColumns());

    if ($this->hasHeaderRow) {
      fputcsv($handle, $header, $this->delimiter, $this->enclosure, $this->escape);
    }

    foreach ($rows as $row) {
      $data = [];
      foreach ($header as $name) {
        $data[] = $row->$name ?? '';
      }

      fputcsv($handle, $data, $this->delimiter, $this->enclosure, $this->escape);
    }

    fclose($handle);
  }
}
