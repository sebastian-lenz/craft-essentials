<?php

namespace lenz\craft\essentials\structs\icon;

use craft\helpers\Html;

/**
 * Class Element
 */
class Element
{
  /**
   * @var array
   */
  public array $attributes;

  /**
   * @var string
   */
  public string $name;


  /**
   * @param string $name
   * @param array $attributes
   */
  public function __construct(string $name, array $attributes = []) {
    $this->name = $name;
    $this->attributes = $attributes;
  }

  /**
   * @return array|null
   */
  public function getSize(): ?array {
    if (preg_match('/(?:(\d+)x)?(\d+)$/', $this->name, $match)) {
      $width = empty($match[1]) ? $match[2] : $match[1];
      $height = $match[2];
      return [intval($width), intval($height)];
    }

    return null;
  }

  /**
   * @param Icon $icon
   * @return string
   */
  public function getTag(Icon $icon): string {
    $attributes = $this->attributes;
    $attributes['href'] = $icon->toElementHref($this->name);

    return Html::tag('use', null, $attributes);
  }
}
