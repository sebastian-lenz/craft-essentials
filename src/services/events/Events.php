<?php

namespace lenz\craft\essentials\services\events;

use yii\base\Component;

/**
 * Class Events
 */
class Events extends Component
{
  /**
   * @var array
   */
  public array $scopes = [];


  /**
   * @return void
   */
  public function init(): void {
    $basePath = \Craft::$app->getPath()->getCompiledClassesPath();
    $fileName = $basePath . '/CompiledEventListeners.php';

    if (!file_exists($fileName)) {
      $listeners = Discovery::find($this->scopes);
      file_put_contents($fileName, Compiler::compile($listeners));
    }

    include_once $fileName;
  }


  // Static methods
  // --------------

  /**
   * @param string $namespace
   * @param string $path
   * @param string|array $segments
   * @return Events
   */
  static public function forNamespace(string $namespace, string $path, string|array $segments = 'listeners'): Events {
    $segments = is_array($segments) ? $segments : explode('/', $segments);

    return new Events([
      'scopes' => [[
        'namespace' => implode('\\', array_filter([$namespace, ...$segments])),
        'path' => implode(DIRECTORY_SEPARATOR, array_filter([$path, ...$segments])),
      ]],
    ]);
  }

  /**
   * @param string $className
   * @return object
   */
  static public function getHandler(string $className): object {
    static $instances = [];
    if (!array_key_exists($className, $instances)) {
      $instances[$className] = \Craft::createObject($className);
    }

    return $instances[$className];
  }
}
