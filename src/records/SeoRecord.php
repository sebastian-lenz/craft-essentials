<?php

namespace lenz\craft\essentials\records;

use craft\db\Migration;
use lenz\craft\utils\foreignField\ForeignFieldRecord;

/**
 * Class SeoRecord
 *
 * @property string $description
 * @property string $keywords
 * @property string $enabled
 */
class SeoRecord extends ForeignFieldRecord
{
  /**
   * @param Migration $migration
   * @param array|null $columns
   */
  public static function createTable(Migration $migration, array $columns = null) {
    if (is_null($columns)) {
      $columns = [
        'enabled'     => $migration->boolean()->defaultValue(1)->notNull(),
        'description' => $migration->text(),
        'keywords'    => $migration->text(),
      ];
    }

    parent::createTable($migration, $columns);
  }

  /**
   * @inheritDoc
   */
  public static function tableName(): string {
    return '{{%lenz_seo}}';
  }
}
