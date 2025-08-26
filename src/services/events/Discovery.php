<?php

namespace lenz\craft\essentials\services\events;

/**
 * Class Discovery
 */
class Discovery
{
  /**
   * @param array $scopes
   * @return listeners\AbstractListener[]
   */
  static public function find(array $scopes): array {
    return array_reduce($scopes, fn(array $listeners, array $scope) =>
      [...$listeners, ...self::findListenersInScope($scope)],
    []);
  }


  // Private methods
  // ---------------

  /**
   * @param array $scope
   * @return listeners\AbstractListener[]
   */
  static private function findListenersInScope(array $scope): array {
    ['namespace' => $namespace, 'path' => $path] = $scope;
    $result = [];

    foreach (scandir($path) as $child) {
      if (str_starts_with($child, '.')) {
        continue;
      }

      $splitAt = strrpos($child, '.');
      $fileName = $path . DIRECTORY_SEPARATOR . $child;
      $className = $namespace . '\\' . ($splitAt === false ? $child : substr($child, 0, $splitAt));

      if (is_dir($fileName)) {
        array_push($result, ...self::findListenersInScope([
          ...$scope,
          'namespace' => $className,
          'path' => $fileName,
        ]));
      } elseif (class_exists($className)) {
        array_push($result, ...self::findListenersInClass($className));
      }
    }

    return $result;
  }

  /**
   * @param string $className
   * @return listeners\AbstractListener[]
   */
  static private function findListenersInClass(string $className): array {
    $reflection = new \ReflectionClass($className);
    $result = array_map(
      fn(\ReflectionAttribute $attribute) => new listeners\ClassListener($attribute, $className),
      $reflection->getAttributes(On::class)
    );

    foreach ($reflection->getMethods() as $method) {
      foreach ($method->getAttributes(On::class) as $attribute) {
        $result[] = new listeners\MethodListener($attribute, $className, $method->name, $method->isStatic());
      }
    }

    return $result;
  }
}
