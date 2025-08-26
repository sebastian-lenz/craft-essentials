<?php

namespace lenz\craft\essentials\services\events\listeners;

use ReflectionAttribute;
use yii\base\Event;

/**
 * Class ClassListener
 */
readonly class ClassListener extends AbstractListener
{
  /**
   * @param ReflectionAttribute $attribute
   * @param string $className
   */
  public function __construct(
    ReflectionAttribute $attribute,
    public string $className
  ) {
    parent::__construct($attribute);
  }

  /**
   * @inheritDoc
   */
  public function register(): void {
    Event::on($this->class, $this->name, $this->className);
  }

  /**
   * @inheritDoc
   */
  public function toCode(): array {
    $class = var_export($this->class, true);
    $name = var_export($this->name, true);
    $className = $this->className;

    return [
      "Event::on($class, $name, $className::class);"
    ];
  }
}
