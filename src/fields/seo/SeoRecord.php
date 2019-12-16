<?php

namespace lenz\craft\essentials\fields\seo;

use craft\db\Migration;
use lenz\craft\utils\foreignField\ForeignFieldRecord;

/**
 * Class SeoRecord
 */
class SeoRecord extends ForeignFieldRecord
{
  /**
   * @param Migration $migration
   * @param array $columns
   */
  public static function createTable(Migration $migration, array $columns = null) {
    if (is_null($columns)) {
      $columns = [
        'description' => $migration->text(),
        'keywords'    => $migration->text(),
      ];
    }

    return parent::createTable($migration, $columns);
  }

  /**
   * @inheritDoc
   */
  public static function tableName() {
    return '{{%lenz_seo}}';
  }
}
