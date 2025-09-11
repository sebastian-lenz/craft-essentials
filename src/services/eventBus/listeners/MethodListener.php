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
    $handler = $this->isStatic
      ? [$this->className, $this->methodName]
      : fn($event) => EventBus::getHandler($this->className)->$methodName($event);

    Event::on($this->class, $this->name, $handler, $this->data, $this->append);
  }

  /**
   * @inheritDoc
   */
  public function toCode(): array {
    $className = $this->className;
    $methodName = $this->methodName;

    if ($this->isStatic) {
      $handler = "$className::$methodName(...)";
    } else {
      $handler = is_callable([$className, 'getInstance'])
        ? "fn(\$event) => $className::getInstance()->$methodName(\$event)"
        : "fn(\$event) => EventBus::getHandler('$className')->$methodName(\$event)";
    }

    return [
      $this->writeOnCall($handler),
    ];
  }
}
