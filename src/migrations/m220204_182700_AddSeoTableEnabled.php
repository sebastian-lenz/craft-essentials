<?php

namespace lenz\craft\essentials\migrations;

use craft\db\Migration;
use lenz\craft\essentials\records\SeoRecord;

/**
 * Class m220204_182700_AddSeoTableEnabled
 */
class m220204_182700_AddSeoTableEnabled extends Migration
{
  /**
   * @inheritdoc
   */
  public function safeUp() {
    $this->addColumn(SeoRecord::tableName(), 'enabled', $this->boolean()->defaultValue(1)->notNull());
  }

  /**
   * @inheritDoc
   */
  public function safeDown(): bool {
    $this->dropColumn(SeoRecord::tableName(), 'enabled');
    return true;
  }
}
