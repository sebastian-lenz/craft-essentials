<?php

namespace lenz\craft\essentials\services\tables;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;
use yii\helpers\Inflector;

/**
 * Class AbstractDataTable
 */
abstract class AbstractTable implements ArrayAccess, Countable, IteratorAggregate
{
  use HandsontableTrait;

  /**
   * @var Row[]
   */
  protected array $_rows;


  /**
   * @return Row[]
   */
  abstract protected function loadRows(): array;

  /**
   * @param Row[] $rows
   */
  abstract protected function saveRows(array $rows);

  /**
   * @param array $attributes
   * @return Row
   */
  public function append(array $attributes): Row {
    $this->load();
    $row = $this->createRow($attributes);

    $this->_rows[] = $row;
    return $row;
  }

  /**
   * @param callable $callback
   * @return Row|null
   */
  public function find(callable $callback): ?Row {
    $this->load();
    foreach ($this->_rows as $row) {
      if ($callback($row)) return $row;
    }

    return null;
  }

  /**
   * @return string
   */
  public function getId(): string {
    return md5(get_called_class());
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
   * @return void
   */
  public function load(): void {
    if (!isset($this->_rows)) {
      $this->_rows = $this->loadRows();
    }
  }

  /**
   * @return void
   */
  public function save(): void {
    if (isset($this->_rows)) {
      $this->saveRows($this->_rows);
    }
  }

  /**
   * @param array $rows
   */
  public function setRows(array $rows): void {
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


  // Interfaces
  // ----------

  /**
   * @inheritDoc
   */
  public function count(): int {
    $this->load();
    return count($this->_rows);
  }

  /**
   * @return Traversable
   */
  public function getIterator(): Traversable {
    $this->load();
    return new ArrayIterator($this->_rows);
  }

  /**
   * @inheritDoc
   */
  public function offsetExists(mixed $offset): bool {
    $this->load();
    return array_key_exists($offset, $this->_rows);
  }

  /**
   * @inheritDoc
   */
  public function offsetGet(mixed $offset): Row {
    $this->load();
    return $this->_rows[$offset];
  }

  /**
   * @inheritDoc
   */
  public function offsetSet(mixed $offset, mixed $value): void {
    $this->load();
    $this->_rows[$offset] = $value;
  }

  /**
   * @inheritDoc
   */
  public function offsetUnset(mixed $offset): void {
    $this->load();
    unset($this->_rows[$offset]);
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
   * @param Row $row
   * @param array $attributes
   * @return array
   */
  protected function getSaveRowData(Row $row, array $attributes): array {
    return $attributes;
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
