<?php

namespace lenz\craft\essentials\structs\icon;

use craft\helpers\Html;
use lenz\craft\essentials\twig\AbstractMarkup;
use lenz\craft\utils\helpers\ArrayHelper;

/**
 * Class IconMarkup
 */
class Icon extends AbstractMarkup
{
  /**
   * @var array
   */
  private array $_attributes;

  /**
   * @var Element[]
   */
  private array $_elements = [];

  /**
   * @var float
   */
  private float $_scale = 1;

  /**
   * @var string|null
   */
  private string|null $_title = null;


  /**
   * Icon constructor
   * @param string|array $content
   * @param array $attributes
   */
  public function __construct(string|array $content = '', $attributes = []) {
    parent::__construct();

    $this
      ->scale(ArrayHelper::remove($attributes, 'scale', 1))
      ->title(ArrayHelper::remove($attributes, 'title'))
      ->attributes($attributes)
      ->content($content);
  }

  /**
   * @param array|string $parts
   * @return $this
   */
  private function append(array|string $parts): static {
    if (!is_array($parts)) {
      $parts = [$parts];
    }

    foreach ($parts as $key => $part) {
      if (is_numeric($key)) {
        $part = is_array($part) ? $part : ['name' => $part];
      } else {
        $part = is_array($part) ? $part : ['class' => $part];
        $part['name'] = $key;
      }

      $name = ArrayHelper::remove($part, 'name');
      if (!empty($name)) {
        $this->_elements[] = new Element($name, $part);
      }
    }

    return $this;
  }

  /**
   * @param array $attributes
   * @return $this
   */
  public function attributes(array $attributes): static {
    $this->_attributes = $attributes;
    return $this;
  }

  /**
   * @return $this
   */
  public function clear(): static {
    $this->_elements = [];
    return $this;
  }

  /**
   * @param array|string $content
   * @return $this
   */
  public function content(array|string $content): static {
    return $this->clear()->append($content);
  }

  /**
   * @return string
   */
  public function createId(): string {
    return uniqid();
  }

  /**
   * @param float $scale
   * @return $this
   */
  public function scale(float $scale): static {
    $this->_scale = $scale;
    return $this;
  }

  /**
   * @param string|null $title
   * @return $this
   */
  public function title(string|null $title): static {
    $this->_title = $title;
    return $this;
  }

  /**
   * @param string $name
   * @return string
   */
  public function toElementHref(string $name): string {
    return '#' . $name;
  }


  // Protected methods
  // -----------------

  /**
   * @return string
   */
  protected function getContent(): string {
    $attributes = $this->_attributes;
    $content = array_map(
      fn(Element $element) => $element->getTag($this),
      $this->_elements
    );

    $this->applySize($attributes);
    $this->applyTitle($attributes, $content);

    return Html::tag('svg', implode('', $content), $attributes);
  }


  // Private methods
  // ---------------

  /**
   * @param array $attributes
   * @return void
   */
  private function applySize(array &$attributes): void {
    $size = $this->getSize();
    if (is_null($size)) {
      return;
    }

    if (!isset($attributes['width']) && !isset($attributes['height'])) {
      $attributes['width'] = $size[0] * $this->_scale;
      $attributes['height'] = $size[1] * $this->_scale;
    }

    if (!isset($attributes['viewBox'])) {
      $attributes['viewBox'] = implode(' ', [0, 0, $size[0], $size[1]]);
    }
  }

  /**
   * @param array $attributes
   * @param array $content
   * @return void
   */
  private function applyTitle(array &$attributes, array &$content): void {
    if (empty($this->_title)) {
      return;
    }

    $id = $this->createId();
    $attributes['aria-labelledby'] = $id;
    array_unshift($content, Html::tag('title', $this->_title, ['id' => $id]));
  }

  /**
   * @return array|null
   */
  private function getSize(): array|null {
    foreach ($this->_elements as $element) {
      $result = $element->getSize();

      if (!is_null($result)) {
        return $result;
      }
    }

    return null;
  }


  // Static methods
  // --------------

  /**
   * @param string|array $content
   * @param array $attributes
   * @return Icon
   */
  static public function create(string|array $content = '', array $attributes = []): static {
    return new static($content, $attributes);
  }
}
