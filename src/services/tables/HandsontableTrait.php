<?php

namespace lenz\craft\essentials\services\tables;

/**
 * Trait HandsontableTrait
 */
trait HandsontableTrait
{
  /**
   * @return Column[]
   */
  abstract function getColumns(): array;

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
   * @return Column
   */
  public function html(): Column {
    return (new Column(['config' => ['renderer' => 'html']]))->readOnly();
  }

  /**
   * @return Column
   */
  public function numeric(): Column {
    return new Column('numeric');
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
}
