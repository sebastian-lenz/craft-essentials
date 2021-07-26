<?php

namespace lenz\craft\essentials\services\tables;

use yii\helpers\Inflector;

/**
 * Class DataColumn
 */
class Column
{
  /**
   * @var array
   */
  public $classNames = [];

  /**
   * @var string
   */
  public $title = '';

  /**
   * @var string
   */
  public $type;

  /**
   * @var int
   */
  public $width = 100;


  /**
   * DataColumn constructor.
   * @param string $type
   */
  public function __construct(string $type = 'text') {
    $this->type = $type;
  }

  /**
   * Supported values: left, center, right, justify, top, middle, bottom
   * @return $this
   */
  public function align(): Column {
    foreach (func_get_args() as $arg) {
      $this->classNames[] = 'ht' . ucfirst($arg);
    }

    return $this;
  }

  /**
   * @param mixed $value
   * @return mixed
   */
  public function filter($value) {
    if ($this->type == 'checkbox') {
      return !!$value;
    }

    return $value;
  }

  /**
   * @param string $name
   * @return array
   */
  public function getJsConfig(string $name): array {
    $config = [
      'data' => $name,
      'type' => $this->type,
    ];

    if (!empty($this->classNames)) {
      $config[] = implode(' ', $this->classNames);
    }

    return $config;
  }

  /**
   * @param string $name
   * @return string
   */
  public function getJsHeader(string $name): string {
    return empty($this->title)
      ? Inflector::humanize($name)
      : $this->title;
  }

  /**
   * @return int
   */
  public function getJsWidth(): int {
    return $this->width;
  }

  /**
   * @param string $value
   * @return $this
   */
  function title(string $value): Column {
    $this->title = $value;
    return $this;
  }

  /**
   * @param string $value
   * @return $this
   */
  function type(string $value): Column {
    $this->type = $value;
    return $this;
  }

  /**
   * @param int $value
   * @return $this
   */
  function width(int $value): Column {
    $this->width = $value;
    return $this;
  }
}
