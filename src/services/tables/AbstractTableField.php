<?php

namespace lenz\craft\essentials\services\tables;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\Serializable;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use craft\helpers\Html;
use lenz\craft\essentials\Plugin;
use Throwable;
use yii\base\Arrayable;

/**
 * Class AbstractTableField
 */
abstract class AbstractTableField extends Field
{
  /**
   * @param Row $row
   * @return string
   */
  abstract function getRowId(Row $row): string;

  /**
   * @param Row $row
   * @return string
   */
  abstract function getRowLabel(Row $row): string;

  /**
   * @return string
   */
  abstract function getTableClass(): string;


  // Public methods
  // --------------

  /**
   * @param string $id
   * @return Row|null
   */
  public function findRowById(string $id): ?Row {
    foreach ($this->getTable()->getRows() as $row) {
      if ($this->getRowId($row) == $id) {
        return $row;
      }
    }

    return null;
  }

  /**
   * @return AbstractTable
   */
  public function getTable(): AbstractTable {
    return Plugin::getInstance()->tables->getTable($this->getTableClass());
  }

  /**
   * @param mixed $value
   * @param ElementInterface|null $element
   * @return string
   * @throws Throwable
   */
  protected function inputHtml($value, ElementInterface $element = null): string {
    $selected = $value instanceof Row ? $this->getRowId($value) : null;
    $id = Html::id($this->handle);
    $options = [];

    if (!$this->required) {
      $options[] = Craft::t('app', '(Please select)');
    }

    foreach ($this->getTable()->getRows() as $row) {
      $options[$this->getRowId($row)] = $this->getRowLabel($row);
    }

    asort($options);

    return Craft::$app->getView()->renderTemplate('lenz-craft-essentials/_tables/field', [
      'class' => 'selectize fullwidth timezone',
      'id' => $id,
      'instructionsId' => "$id-instructions",
      'name' => $this->handle,
      'options' => $options,
      'value' => $selected,
    ]);
  }

  /**
   * @inheritDoc
   */
  public function normalizeValue($value, ElementInterface $element = null) {
    if ($value instanceof Row) {
      return $value;
    }

    return is_string($value)
      ? $this->findRowById($value)
      : null;
  }

  /**
   * @inheritDoc
   */
  public function serializeValue($value, ElementInterface $element = null) {
    return $value instanceof Row
      ? $this->getRowId($value)
      : null;
  }


  // Static methods
  // --------------

  /**
   * @return string
   */
  public static function valueType(): string {
    return '?Row';
  }
}
