<?php

namespace lenz\craft\essentials\migrations;

use craft\db\Migration;
use craft\db\Table;
use lenz\craft\essentials\records\SeoRecord;
use lenz\craft\essentials\records\UriHistoryRecord;

/**
 * Class Install
 */
class Install extends Migration
{
  /**
   * @inheritdoc
   */
  public function safeUp() {
    $this->createUriHistoryTable();
    $this->createSeoTable();
  }

  /**
   * @inheritdoc
   */
  public function safeDown() {
    $this->dropTableIfExists(SeoRecord::tableName());
    $this->dropTableIfExists(UriHistoryRecord::tableName());
    return true;
  }


  // Private methods
  // ---------------

  /**
   * @return void
   * @noinspection DuplicatedCode
   */
  private function createUriHistoryTable(): void {
    $table = UriHistoryRecord::tableName();
    if ($this->db->tableExists(UriHistoryRecord::tableName())) {
      return;
    }

    $this->createTable($table, [
      'id'          => $this->primaryKey(),
      'siteId'      => $this->integer()->notNull(),
      'elementId'   => $this->integer()->notNull(),
      'uri'         => $this->string(),
      'dateCreated' => $this->dateTime()->notNull(),
      'dateUpdated' => $this->dateTime()->notNull(),
      'uid'         => $this->uid(),
    ]);

    $this->createIndex(null, $table, ['elementId']);
    $this->createIndex(null, $table, ['uri']);

    $this->addForeignKey(null, $table, ['elementId'], Table::ELEMENTS, ['id'], 'CASCADE');
  }

  /**
   * @return void
   */
  private function createSeoTable(): void {
    if ($this->db->tableExists(SeoRecord::tableName())) {
      return;
    }

    SeoRecord::createTable($this);
  }
}
