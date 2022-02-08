<?php

namespace lenz\craft\essentials\twig\queries\filters;

use craft\helpers\ArrayHelper;
use craft\helpers\Html;
use lenz\contentfield\twig\DisplayInterface;
use lenz\craft\essentials\twig\queries\options\Option;
use lenz\craft\essentials\twig\queries\options\OptionInterface;
use lenz\craft\utils\models\UrlParameter;

/**
 * Class AbstractOptionsFilter
 */
abstract class AbstractOptionsFilter extends AbstractValueFilter implements DisplayInterface
{
  /**
   * @var string
   */
  public $displayFormat = '"%s"';

  /**
   * @var string[]|int[]|null
   */
  protected $_customValues = null;

  /**
   * @var string[]|int[]|null
   */
  protected $_fixedValues = null;

  /**
   * @var OptionInterface[]
   */
  protected $_options;

  /**
   * @var string
   */
  const GLUE = ',';


  /**
   * @inheritDoc
   */
  public function allowCustomFilter() {
    return is_null($this->_fixedValues);
  }

  /**
   * @param array $variables
   */
  public function display(array $variables = []) {
    echo $this->renderSelect($variables);
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
    $selectedIds = $this->_customValues;
    if (is_null($this->_customValues) || !is_array($selectedIds)) {
      return null;
    }

    $result = [];
    foreach ($this->getOptions() as $option) {
      if (in_array($option->value, $selectedIds)) {
        $result[] = sprintf($this->displayFormat, $option->title);
      }
    }

    return count($result) > 0 ? join(', ', $result) : null;
  }

  /**
   * @return string[]|int[]
   */
  public function getOptionValues() {
    return array_map(function(OptionInterface $option) {
      return $option->getOptionValue();
    }, $this->getOptions());
  }

  /**
   * @return OptionInterface[]
   */
  public function getOptions() {
    return $this->_options;
  }

  /**
   * @return string|null
   */
  public function getValue() : ?UrlParameter {
    if (is_null($this->_customValues)) {
      return null;
    }

    $values = array_map('urlencode', array_filter($this->_customValues));
    return empty($values)
      ? null
      : new UrlParameter(implode(static::GLUE, $values));
  }

  /**
   * @return string[]|int[]
   */
  public function getSelectedValues() {
    if (!is_null($this->_fixedValues)) {
      return $this->_fixedValues;
    }

    if (!is_null($this->_customValues)) {
      return $this->_customValues;
    }

    return [];
  }

  /**
   * @param OptionInterface $option
   * @return bool
   */
  public function isSelected(OptionInterface $option): bool {
    $value = $option->getOptionValue();
    $selected = $this->getSelectedValues();

    return in_array($value, $selected) || (empty($value) && empty($selected));
  }

  /**
   * @param array $variables
   * @return string
   * @throws \Exception
   */
  public function renderSelect(array $variables = []) {
    $options = $this->renderOptions();
    $attributes = ArrayHelper::getValue($variables, 'attributes', []) + [
        'id'   => $this->getName(),
        'name' => $this->getName(),
      ];

    return Html::tag('select', implode('', $options), $attributes);
  }

  /**
   * @param OptionInterface $option
   * @param array $selected
   * @return string
   */
  public function renderOption(OptionInterface $option, array $selected) {
    $value = $option->getOptionValue();

    return Html::tag('option', $option->getOptionTitle(), [
      'selected' => in_array($value, $selected),
      'value' => $value,
    ]);
  }

  /**
   * @return array[]
   */
  public function renderOptions() {
    $selected = $this->getSelectedValues();
    $options = array_map(function(OptionInterface $option) use ($selected) {
      return $this->renderOption($option, $selected);
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
   * @param string[]|int[]|null $value
   */
  public function setFixedValues(array $value = null) {
    $this->_fixedValues = is_array($value) && count($value) > 0
      ? $value
      : null;
  }

  /**
   * @param array $values
   */
  public function setOptions(array $values) {
    $options = [];
    foreach ($values as $key => $value) {
      if ($value instanceof OptionInterface) {
        $options[] = $value;
      } else {
        $options[] = new Option([
          'value' => $key,
          'title' => $value,
        ]);
      }
    }

    $this->_options = $options;
  }

  /**
   * @inheritDoc
   */
  public function setValue(string $value) {
    $result = [];
    $options = $this->getOptionValues();

    foreach (explode(static::GLUE, $value) as $item) {
      if (in_array($item, $options)) {
        $result[] = $item;
      }
    }

    $this->_customValues = count($result) > 0
      ? $result
      : null;
  }
}
