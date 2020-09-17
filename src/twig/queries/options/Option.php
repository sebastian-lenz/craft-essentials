<?php

namespace lenz\craft\essentials\twig\queries\options;

use craft\helpers\Html;
use yii\base\BaseObject;

/**
 * Class Option
 */
class Option extends BaseObject
{
  /**
   * @var int
   */
  public $id;

  /**
   * @var string
   */
  public $title;


  /**
   * @param array $attributes
   * @return string
   */
  public function getSelectOption($attributes = []) {
    return Html::tag('option', $this->title, $attributes + [
      'value' => $this->id,
    ]);
  }
}
