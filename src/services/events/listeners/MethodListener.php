<?php

namespace lenz\craft\essentials\services\events\listeners;

use lenz\craft\essentials\services\events\Events;
use ReflectionAttribute;
use yii\base\Event;

/**
 * Class MethodListener
 */
readonly class MethodListener extends AbstractListener
{
  /**
   * @param ReflectionAttribute $attribute
   * @param string $className
   * @param string $methodName
   * @param bool $isStatic
   */
  public function __construct(
    ReflectionAttribute $attribute,
    public string $className,
    public string $methodName,
    public bool $isStatic,
  ) {
    parent::__construct($attribute);
  }

  /**
   * @inheritDoc
   */
  public function register(): void {
    $methodName = $this->methodName;

    if ($this->isStatic) {
      Event::on($this->class, $this->name, [$this->className, $this->methodName]);
    } else {
      Event::on($this->class, $this->name, fn($event) =>
        Events::getHandler($this->className)->$methodName($event)
      );
    }
  }

  /**
   * @inheritDoc
   */
  public function toCode(): array {
    $class = var_export($this->class, true);
    $name = var_export($this->name, true);
    $className = $this->className;
    $methodName = $this->methodName;

    return [
      $this->isStatic
        ? "Event::on($class, $name, $className::$methodName(...));"
        : "Event::on($class, $name, fn(\$event) => Events::getHandler('$className')->$methodName(\$event));"
    ];
  }
}
