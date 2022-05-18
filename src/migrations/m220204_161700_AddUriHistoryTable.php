<?php

namespace lenz\craft\essentials\migrations;

use craft\db\Migration;
use craft\db\Table;
use lenz\craft\essentials\records\UriHistoryRecord;

/**
 * Class m220204_161700_AddUriHistoryTable
 */
class m220204_161700_AddUriHistoryTable extends Migration
{
  /**
   * @inheritdoc
   * @noinspection DuplicatedCode
   */
  public function safeUp() {
    $table = UriHistoryRecord::tableName();
    if ($this->db->tableExists($table)) {
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

    $this->addForeignKey(null, $table, ['elementId'], Table::ELEMENTS, ['id'], 'CASCADE', null);
  }

  /**
   * @inheritDoc
   */
  public function safeDown(): bool {
    $this->dropTableIfExists(UriHistoryRecord::tableName());
    return true;
  }
}
