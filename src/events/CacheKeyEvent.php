<?php

namespace lenz\craft\essentials\events;

use Craft;
use craft\base\Element;
use craft\web\Request;
use lenz\craft\essentials\Plugin;
use Throwable;
use yii\base\Event;

/**
 * Class FrontendCacheRequestEvent
 */
class CacheKeyEvent extends Event
{
  /**
   * @var string|null
   */
  public ?string $cacheKey = null;


  /**
   * FrontendCacheRequestEvent constructor.
   */
  public function __construct() {
    parent::__construct();
    $this->handled = $this->generateCacheKey();
  }


  // Private methods
  // ---------------

  /**
   * @return bool
   */
  private function generateCacheKey(): bool {
    $request = Craft::$app->request;
    if (!($request instanceof Request)) {
      return true;
    }

    $route = Craft::$app->requestedRoute;
    if (!in_array($route, Plugin::getInstance()->getSettings()->cachedRoutes)) {
      return true;
    }

    $this->cacheKey = $route == 'templates/render'
      ? self::getUrlCacheKey()
      : self::getRouteCacheKey();

    return false;
  }


  // Static methods
  // --------------

  /**
   * @param array $items
   * @param string $path
   * @return array|false
   */
  static public function getFlattened(array $items, string $path = ''): array|false {
    $result = [];

    foreach ($items as $key => $item) {
      if ($item instanceof Element) {
        $result[] = $path . $key . '=' . $item->getId();
      } elseif (is_string($item) || is_numeric($item)) {
        $result[] = $path . $key . '=' . $item;
      } elseif (is_array($item)) {
        $flattened = self::getFlattened($item, $path . $key . '.');
        if ($flattened === false) return false;
        $result = array_merge($result, $flattened);
      } else {
        return false;
      }
    }

    sort($result);
    return $result;
  }

  /**
   * @return string|null
   */
  static public function getUrlCacheKey(): ?string {
    try {
      $result = 'url:' . Craft::$app->request->getUrl();
      try {
        $site = Craft::$app->getSites()->getCurrentSite();
        return 'site:' . $site->handle . ';' . $result;
      } catch (Throwable) {
        return $result;
      }
    } catch (Throwable) {
      return null;
    }
  }

  /**
   * @return string
   */
  static public function getRouteCacheKey(): string {
    $request = Craft::$app->request;
    $params  = Craft::$app->getUrlManager()->getRouteParams();
    if (!is_array($params)) {
      $params = [];
    }

    $params += $request->getQueryParams();
    $parts = self::getFlattened($params);

    return 'route:' . implode(';', $parts);
  }
}
