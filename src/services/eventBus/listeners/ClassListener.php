<?php

namespace lenz\craft\essentials\services\eventBus\listeners;

use lenz\craft\essentials\services\eventBus\On;
use ReflectionAttribute;
use yii\base\Event;

/**
 * Class ClassListener
 */
readonly class ClassListener extends AbstractListener
{
  /**
   * @param On $decorator
   * @param string $className
   */
  public function __construct(
    On $decorator,
    public string $className
  ) {
    parent::__construct($decorator);
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
