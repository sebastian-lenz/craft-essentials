<?php

namespace lenz\craft\essentials\services\tables;

use craft\db\ActiveRecord;

/**
 * Class AbstractRecordDataTable
 * @noinspection PhpUnused
 */
abstract class AbstractRecordTable extends AbstractTable
{
  /**
   * @return class-string<ActiveRecord>
   */
  abstract function getRecordClass(): string;


  // Protected methods
  // -----------------

  /**
   * @param ActiveRecord $record
   * @param Row $row
   * @return bool
   * @noinspection PhpUnusedParameterInspection
   */
  protected function isRecordEqual(ActiveRecord $record, Row $row): bool {
    return false;
  }

  /**
   * @inheritDoc
   */
  protected function loadRows(): array {
    $recordClass = $this->getRecordClass();
    $records = $recordClass::find()->all();
    $columns = $this->getColumns();
    $rows = [];

    foreach ($records as $record) {
      $attributes = [];
      foreach ($columns as $name => $column) {
        $attributes[$name] = $column->filter($record[$name]);
      }

      $rows[] = $this->createRow($attributes);
    }

    return $rows;
  }

  /**
   * @inheritDoc
   */
  protected function saveRows(array $rows) {
    $recordClass = $this->getRecordClass();
    $records = $recordClass::find()->all();

    foreach ($rows as $row) {
      $data = $this->getSaveRowData($row, $row->attributes);

      foreach ($records as $index => $record) {
        if ($this->isRecordEqual($record, $row)) {
          $record->setAttributes($data);
          $record->save();

          array_splice($records, $index, 1);
          continue 2;
        }
      }

      $record = new $recordClass($data);
      $record->save();
    }

    foreach ($records as $record) {
      $record->delete();
    }
  }
}
