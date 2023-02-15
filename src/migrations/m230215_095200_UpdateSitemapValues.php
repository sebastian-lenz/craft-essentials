<?php

namespace lenz\craft\essentials\migrations;

use craft\db\Migration;
use lenz\craft\essentials\records\SeoRecord;

/**
 * Class m230215_095200_UpdateSitemapValues
 */
class m230215_095200_UpdateSitemapValues extends Migration
{
  /**
   * @inheritdoc
   */
  public function safeUp() {
    $this->update(SeoRecord::tableName(), ['enabled' => 1]);
  }

  /**
   * @inheritDoc
   */
  public function safeDown(): bool {
    return true;
  }
}
