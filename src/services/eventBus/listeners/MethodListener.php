<?php

namespace lenz\craft\essentials\services\eventBus\listeners;

use lenz\craft\essentials\services\eventBus\EventBus;
use lenz\craft\essentials\services\eventBus\On;
use ReflectionMethod;
use yii\base\Event;

/**
 * Class MethodListener
 */
readonly class MethodListener extends AbstractListener
{
  /**
   * @var string
   */
  public string $methodName;

  /**
   * @var bool
   */
  public bool $isStatic;


  /**
   * @param On $decorator
   * @param string $className
   * @param ReflectionMethod $method
   */
  public function __construct(
    On $decorator,
    public string $className,
    ReflectionMethod $method
  ) {
    parent::__construct($decorator);

    $this->methodName = $method->name;
    $this->isStatic = $method->isStatic();
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
        EventBus::getHandler($this->className)->$methodName($event)
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

    if ($this->isStatic) {
      return ["Event::on($class, $name, $className::$methodName(...));"];
    }

    return [
      is_callable([$className, 'getInstance'])
        ? "Event::on($class, $name, fn(\$event) => $className::getInstance()->$methodName(\$event));"
        : "Event::on($class, $name, fn(\$event) => EventBus::getHandler('$className')->$methodName(\$event));"
    ];
  }
}
