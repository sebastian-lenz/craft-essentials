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
   * @var array
   */
  private array $_scopes = [];


  /**
   * @inheritDoc
   */
  public function init(): void {
    parent::init();

    $basePath = \Craft::$app->getPath()->getCompiledClassesPath();
    $fileName = $basePath . '/compiled_event_listeners.php';

    if (!file_exists($fileName)) {
      Event::on(Application::class, WebApplication::EVENT_INIT, function () use ($fileName) {
        file_put_contents($fileName, Compiler::compile($this->findListeners()));
        include_once $fileName;
      });
    } else {
      include_once $fileName;
    }
  }

  /**
   * @param string ...$classNames
   * @return EventBus
   */
  public function addClass(...$classNames): EventBus {
    foreach ($classNames as $className) {
      $this->_scopes[] = new ClassScope($className);
    }

    return $this;
  }

  /**
   * @param string $namespace
   * @param string $path
   * @param string|array $segments
   * @return EventBus
   */
  public function addNamespace(string $namespace, string $path, string|array $segments = 'listeners'): EventBus {
    $segments = is_array($segments) ? $segments : explode('/', $segments);
    $this->_scopes[] = new NamespaceScope(
      namespace: implode('\\', array_filter([$namespace, ...$segments])),
      path: implode(DIRECTORY_SEPARATOR, array_filter([$path, ...$segments])),
    );

    return $this;
  }


  // Private methods
  // ---------------

  /**
   * @return AbstractListener[]
   */
  private function findListeners(): array {
    return array_reduce(
      $this->_scopes,
      fn(array $listener, AbstractScope $scope) => [
        ...$listener,
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
