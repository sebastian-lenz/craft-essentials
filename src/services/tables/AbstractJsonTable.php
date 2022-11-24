<?php

namespace lenz\craft\essentials\services\tables;

use craft\helpers\App;
use craft\helpers\Json;
use Throwable;

/**
 * Class AbstractJsonDataTable
 * @noinspection PhpUnused
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
    $fileName = App::parseEnv($this->getFileName());

    try {
      return array_map(function($data) {
        return $this->createRow($data);
      }, Json::decode(file_get_contents($fileName)));
    } catch (Throwable) {
      return [];
    }
  }

  /**
   * @inheritDoc
   */
  protected function saveRows(array $rows) {
    $fileName = App::parseEnv($this->getFileName());

    file_put_contents($fileName, Json::encode(array_map(function($row) {
      return $this->getSaveRowData($row, $row->attributes);
    }, $rows), JSON_PRETTY_PRINT));
  }
}
