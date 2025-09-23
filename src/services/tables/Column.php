<?php

namespace lenz\craft\essentials\services\tables;

use yii\base\BaseObject;
use yii\helpers\Inflector;

/**
 * Class DataColumn
 */
class Column extends BaseObject
{
  /**
   * @var array
   */
  public array $classNames = [];

  /**
   * @var array|null
   */
  public ?array $config = null;

  /**
   * @var callable[]|null
   */
  public ?array $filters = null;

  /**
   * @var array|null
   */
  public ?array $source = null;

  /**
   * @var bool
   */
  public bool $readOnly = false;

  /**
   * @var string
   */
  public string $title = '';

  /**
   * @var string
   */
  public string $type;

  /**
   * @var int
   */
  public int $width = 100;


  /**
   * DataColumn constructor.
   * @param string|array $typeOrConfig
   */
  public function __construct($typeOrConfig = 'text') {
    parent::__construct(is_array($typeOrConfig)
      ? array_merge(['type' => 'text'], $typeOrConfig)
      : ['type' => $typeOrConfig]
    );
  }

  /**
   * @param callable $callback
   * @param FilterType $type
   * @return $this
   */
  public function addFilter(callable $callback, FilterType $type = FilterType::Any): self {
    $this->filters[] = new Filter($callback, $type);
    return $this;
  }

  /**
   * @param callable $fromFilter
   * @param callable $toFilter
   * @return $this
   */
  public function addStorageFilter(callable $fromFilter, callable $toFilter): self {
    $this->filters[] = new Filter($fromFilter, FilterType::FromStorage);
    $this->filters[] = new Filter($toFilter, FilterType::ToStorage);
    return $this;
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
   * @param FilterType $type
   * @return mixed
   */
  public function filter(mixed $value, FilterType $type): mixed {
    if ($this->type == 'checkbox') {
      return !!$value;
    }

    if (!empty($this->filters)) {
      foreach ($this->filters as $filter) {
        if (!($filter instanceof Filter)) {
          $value = $filter($value);
        } elseif (
          $filter->type == $type ||
          $filter->type == FilterType::Any ||
          $type == FilterType::Any
        ) {
          $value = $filter($value);
        }
      }
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

    if ($this->readOnly) {
      $config['readOnly'] = true;
    }

    if (!is_null($this->source)) {
      $config['source'] = $this->source;
    }

    if (!is_null($this->config)) {
      $config = array_merge($config, $this->config);
    }

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
   * @param bool $value
   * @return $this
   */
  function readOnly(bool $value = true): Column {
    $this->readOnly = $value;
    return $this;
  }

  /**
   * @param array $value
   * @return $this
   */
  function source(array $value): Column {
    $this->source = $value;
    return $this;
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
