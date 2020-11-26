<?php

namespace lenz\craft\essentials\twig\queries\options;

use yii\base\BaseObject;

/**
 * Class Option
 */
class Option extends BaseObject implements OptionInterface
{
  /**
   * @var string|int
   */
  public $value;

  /**
   * @var string
   */
  public $title;


  /**
   * @inheritDoc
   */
  function getOptionValue() {
    return $this->value;
  }

  /**
   * @inheritDoc
   */
  function getOptionTitle(): string {
    return $this->title;
  }
}
