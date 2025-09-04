<?php

namespace lenz\craft\essentials\services\eventBus\scopes;

use lenz\craft\essentials\services\eventBus\listeners\AbstractListener;
use lenz\craft\essentials\services\eventBus\listeners\ClassListener;
use lenz\craft\essentials\services\eventBus\listeners\MethodListener;
use lenz\craft\essentials\services\eventBus\On;

/**
 * Class ClassScope
 */
class ClassScope extends AbstractScope
{
  /**
   * @param string $className
   */
  public function __construct(
    public readonly string $className)
  { }

  /**
   * @return array|AbstractListener[]
   */
  function findListeners(): array {
    return self::toListeners($this->className);
  }


  // Static methods
  // --------------

  /**
   * @param string $className
   * @return AbstractListener[]
   */
  static public function toListeners(string $className): array {
    $reflection = new \ReflectionClass($className);
    $listeners = collect($reflection->getAttributes(On::class))
      ->map(fn(\ReflectionAttribute $attribute) => $attribute->newInstance())
      ->filter(fn(On $decorator) => $decorator->isEnabled($className))
      ->map(fn(On $decorator) => new ClassListener($decorator, $className))
      ->all();

    foreach ($reflection->getMethods() as $method) {
      foreach ($method->getAttributes(On::class) as $attribute) {
        $decorator = $attribute->newInstance();
        if ($decorator->isEnabled($className)) {
          $listeners[] = new MethodListener($decorator, $className, $method);
        }
      }
    }

    return $listeners;
  }
}
