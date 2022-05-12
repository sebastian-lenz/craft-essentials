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
  public string|int $value;

  /**
   * @var string
   */
  public string $title;


  /**
   * @inheritDoc
   */
  function getOptionValue(): int|string {
    return $this->value;
  }

  /**
   * @inheritDoc
   */
  function getOptionTitle(): string {
    return $this->title;
  }
}
