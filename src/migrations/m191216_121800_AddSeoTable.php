<?php

namespace lenz\craft\essentials\migrations;

use craft\db\Migration;
use lenz\craft\essentials\fields\seo\SeoRecord;

/**
 * Class m191216_121800_AddSeoTable
 */
class m191216_121800_AddSeoTable extends Migration
{
  /**
   * @inheritdoc
   */
  public function safeUp() {
    SeoRecord::createTable($this, [
      'description' => $this->text(),
      'keywords'    => $this->text(),
    ]);
  }

  /**
   * @inheritdoc
   */
  public function safeDown() {
    return false;
  }
}
