<?php

namespace lenz\craft\essentials\twig\queries\filters;

use craft\helpers\ArrayHelper;
use craft\helpers\Html;
use lenz\contentfield\twig\DisplayInterface;
use lenz\craft\essentials\twig\queries\options\Option;

/**
 * Class AbstractOptionsFilter
 */
abstract class AbstractOptionsFilter extends AbstractFilter implements DisplayInterface
{
  /**
   * @var int[]|null
   */
  protected $_customIds = null;

  /**
   * @var int[]|null
   */
  protected $_fixedIds = null;

  /**
   * @var Option[]
   */
  protected $_options;


  /**
   * @inheritDoc
   */
  public function allowCustomFilter() {
    return is_null($this->_fixedIds);
  }

  /**
   * @param array $variables
   */
  public function display(array $variables = []) {
    $options = $this->getSelectOptions();
    $attributes = ArrayHelper::getValue($variables, 'attributes', []) + [
      'id'   => $this->getName(),
      'name' => $this->getName(),
    ];

    echo Html::tag('select', implode('', $options), $attributes);
  }

  /**
   * @return string|null
   */
  public function getAllOption() {
    return null;
  }

  /**
   * @return string|null
   */
  public function getDescription() {
    $selectedIds = $this->_customIds;
    if (is_null($this->_customIds) || !is_array($selectedIds)) {
      return null;
    }

    $result = [];
    foreach ($this->getOptions() as $option) {
      if (in_array($option->id, $selectedIds)) {
        $result[] = '"' . $option->title . '"';
      }
    }

    return count($result) > 0 ? join(', ', $result) : null;
  }

  /**
   * @return int[]
   */
  public function getOptionIds() {
    return array_map(function(Option $option) {
      return $option->id;
    }, $this->getOptions());
  }

  /**
   * @return Option[]
   */
  public function getOptions() {
    return $this->_options;
  }

  /**
   * @return string|null
   */
  public function getQueryParameter() {
    return is_null($this->_customIds)
      ? null
      : implode(',', $this->_customIds);
  }

  /**
   * @return int[]
   */
  public function getSelectedIds() {
    if (!is_null($this->_fixedIds)) {
      return $this->_fixedIds;
    }

    if (!is_null($this->_customIds)) {
      return $this->_customIds;
    }

    return [];
  }

  /**
   * @param Option $option
   * @return bool
   */
  public function isSelected(Option $option) {
    return in_array($option->id, $this->getSelectedIds());
  }

  /**
   * @return array[]
   */
  public function getSelectOptions() {
    $selected = $this->getSelectedIds();
    $options = array_map(function(Option $option) use ($selected) {
      return $option->getSelectOption([
        'selected' => in_array($option->id, $selected),
      ]);
    }, $this->getOptions());

    $all = $this->getAllOption();
    if (!is_null($all)) {
      array_unshift($options, Html::tag('option', $all, [
        'selected' => empty($selected),
        'value'    => '',
      ]));
    }

    return $options;
  }

  /**
   * @param int[]|null $value
   */
  public function setFixedIds(array $value = null) {
    $this->_fixedIds = is_array($value) && count($value) > 0
      ? $value
      : null;
  }

  /**
   * @param array $options
   */
  public function setOptions(array $options) {
    $staticOptions = [];

    foreach ($options as $key => $value) {
      if ($value instanceof Option) {
        $staticOptions[] = $value;
      } else {
        $staticOptions[] = new Option([
          'id'    => $key,
          'title' => $value,
        ]);
      }
    }

    $this->_options = $staticOptions;
  }

  /**
   * @inheritDoc
   */
  public function setQueryParameter($value) {
    $result = [];
    $ids    = $this->getOptionIds();

    foreach (explode(',', $value) as $id) {
      $id = intval($id);
      if (in_array($id, $ids)) {
        $result[] = $id;
      }
    }

    $this->_customIds = count($result) > 0
      ? $result
      : null;
  }
}
