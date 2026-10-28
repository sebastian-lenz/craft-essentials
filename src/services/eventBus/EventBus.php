<?php

namespace lenz\craft\essentials\services\eventBus;

use craft\web\Application as WebApplication;
use lenz\craft\essentials\services\eventBus\listeners\AbstractListener;
use lenz\craft\essentials\services\eventBus\scopes\AbstractScope;
use lenz\craft\essentials\services\eventBus\scopes\ClassScope;
use lenz\craft\essentials\services\eventBus\scopes\NamespaceScope;
use yii\base\Application;
use yii\base\Component;
use yii\base\Event;

\Craft::$container->setSingleton(EventBus::class);

/**
 * Class EventBus
 */
class EventBus extends Component
{
  /**
   * @param string ...$classNames
   * @return EventBus
   */
  public function addClass(...$classNames): EventBus {
    return $this->addScopes(array_map(
      fn(string $className) => new ClassScope($className),
      $classNames
    ));
  }

  /**
   * @param string $namespace
   * @param string $path
   * @param string|array $segments
   * @return EventBus
   */
  public function addNamespace(string $namespace, string $path, string|array $segments = 'listeners'): EventBus {
    $segments = is_array($segments) ? $segments : explode('/', $segments);
    return $this->addScopes([
      new NamespaceScope(
        namespace: implode('\\', array_filter([$namespace, ...$segments])),
        path: implode(DIRECTORY_SEPARATOR, array_filter([$path, ...$segments])),
      ),
    ]);
  }


  // Private methods
  // ---------------

  /**
   * Every call gets a compiled file of its own, so the listeners of a scope are
   * only registered by the module that added it. A shared file would register the
   * listeners of a plugin through any other module using the bus, even when the
   * plugin itself is not loaded. Naming the file after its scopes also replaces
   * it as soon as the scopes change.
   *
   * @param AbstractScope[] $scopes
   * @return EventBus
   */
  private function addScopes(array $scopes): EventBus {
    $basePath = \Craft::$app->getPath()->getCompiledClassesPath();
    $fileName = $basePath . '/compiled_event_listeners_' . md5(serialize($scopes)) . '.php';

    if (file_exists($fileName)) {
      include_once $fileName;
    } else {
      Event::on(Application::class, WebApplication::EVENT_INIT, function() use ($fileName, $scopes) {
        file_put_contents($fileName, Compiler::compile($this->findListeners($scopes)));
        include_once $fileName;
      });
    }

    return $this;
  }

  /**
   * @param AbstractScope[] $scopes
   * @return AbstractListener[]
   */
  private function findListeners(array $scopes): array {
    return array_reduce(
      $scopes,
      fn(array $listeners, AbstractScope $scope) => [
        ...$listeners,
        ...$scope->findListeners()
      ],
      []
    );
  }


  // Static methods
  // --------------

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

  /**
   * @return EventBus
   */
  static public function getInstance(): EventBus {
    return \Craft::createObject(self::class);
  }
}
