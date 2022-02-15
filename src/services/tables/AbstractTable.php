<?php

namespace lenz\craft\essentials\services\tables;

use yii\helpers\Inflector;

/**
 * Class AbstractDataTable
 */
abstract class AbstractTable
{
  /**
   * @var Row[]
   */
  private $_rows;


  /**
   * @return Column[]
   */
  abstract public function getColumns(): array;

  /**
   * @return Row[]
   */
  abstract protected function loadRows(): array;

  /**
   * @param Row[] $rows
   */
  abstract protected function saveRows(array $rows);


  // Public methods
  // --------------

  /**
   * @return Column
   */
  public function checkbox(): Column {
    return new Column('checkbox');
  }

  /**
   * @return Column
   */
  public function date(): Column {
    return new Column('date');
  }

  /**
   * @param array $options
   * @return Column
   */
  public function dropdown(array $options): Column {
    return new Column([
      'type' => 'dropdown',
      'source' => array_values($options),
    ]);
  }

  /**
   * @return string
   */
  public function getId(): string {
    return md5(get_called_class());
  }

  /**
   * @return array
   * @noinspection PhpUnused (Template method)
   */
  public function getJsOptions(): array {
    $columns = [];
    $colHeaders = [];
    $colWidths = [];

    foreach ($this->getColumns() as $name => $column) {
      $columns[] = $column->getJsConfig($name);
      $colHeaders[] = $column->getJsHeader($name);
      $colWidths[] = $column->getJsWidth();
    }

    return [
      'columns' => $columns,
      'colHeaders' => $colHeaders,
      'colWidths' => $colWidths,
    ];
  }

  /**
   * @return string
   */
  public function getLabel(): string {
    $label = get_called_class();
    $pos = strrpos($label, '\\');
    if ($pos !== false) {
      $label = substr($label, $pos + 1);
    }

    return Inflector::camel2words(preg_replace('/Table$/', '', $label));
  }

  /**
   * @return Row[]
   */
  public function getRows(): array {
    if (!isset($this->_rows)) {
      $this->_rows = $this->loadRows();
    }

    return $this->_rows;
  }

  /**
   * @return Column
   */
  public function numeric(): Column {
    return new Column('numeric');
  }

  /**
   * @param array $rows
   */
  public function setRows(array $rows) {
    $columns = $this->getColumns();
    $result = [];

    foreach ($rows as $row) {
      if (is_array($row)) {
        $row = $this->createRowFromInput($columns, $row);
      }

      if (!($row instanceof Row)) {
        continue;
      }

      foreach ($result as $index => $existingRow) {
        if ($this->isRowEqual($row, $existingRow)) {
          $result[$index] = $row;
          continue 2;
        }
      }

      $result[] = $row;
    }

    $result = $this->_rows = $this->filterRows($result);
    $this->saveRows($result);
  }

  /**
   * @return Column
   */
  public function text(): Column {
    return new Column('text');
  }

  /**
   * @return Column
   */
  public function time(): Column {
    return new Column('time');
  }


  // Protected methods
  // -----------------

  /**
   * @return string
   */
  protected function getRowClass(): string {
    return Row::class;
  }

  /**
   * @param Row $lft
   * @param Row $rgt
   * @return bool
   * @noinspection PhpUnusedParameterInspection (API)
   */
  protected function isRowEqual(Row $lft, Row $rgt): bool {
    return false;
  }

  /**
   * @param array $attributes
   * @return Row|null
   */
  protected function createRow(array $attributes): ?Row {
    $rowClass = $this->getRowClass();
    return new $rowClass($attributes);
  }

  /**
   * @param Column[] $columns
   * @param array $data
   * @return Row|null
   */
  protected function createRowFromInput(array $columns, array $data): ?Row {
    $attributes = [];
    foreach ($columns as $name => $column) {
      if (!array_key_exists($name, $data)) {
        continue;
      }

      $value = trim($data[$name]);
      if (empty($value)) {
        continue;
      }

      $attributes[$name] = $column->filter($value);
    }

    return $this->createRow($attributes);
  }

  /**
   * @param Row[] $rows
   * @return Row[]
   */
  protected function filterRows(array $rows): array {
    return $rows;
  }
}
