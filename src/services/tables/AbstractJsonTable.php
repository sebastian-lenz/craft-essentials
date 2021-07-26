<?php

namespace lenz\craft\essentials\services\tables;

use Craft;
use craft\helpers\Json;
use Throwable;

/**
 * Class AbstractJsonDataTable
 */
abstract class AbstractJsonTable extends AbstractTable
{
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

    try {
      $rows = array_map(function($data) {
        return $this->createRow($data);
      }, Json::decode(file_get_contents($fileName)));
    } catch (Throwable $error) {
      $rows = [];
    }

    return is_array($rows) ? $rows : [];
  }

  /**
   * @inheritDoc
   */
  protected function saveRows(array $rows) {
    $fileName = Craft::parseEnv($this->getFileName());

    file_put_contents($fileName, Json::encode(array_map(function($row) {
      return $row->attributes;
    }, $rows), JSON_PRETTY_PRINT));
  }
}
