<?php

namespace lenz\craft\essentials\migrations;

use craft\db\Migration;
use lenz\craft\essentials\fields\seo\SeoRecord;

/**
 * Class Install
 */
class Install extends Migration
{
  /**
   * @inheritdoc
   */
  public function safeUp() {
    SeoRecord::createTable($this);
  }

  /**
   * @inheritdoc
   */
  public function safeDown() {
    return false;
  }
}
