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
  public $cacheKey = null;


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
  private function generateCacheKey() {
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
  static public function getFlattened($items, $path = '') {
    $result = array();

    foreach ($items as $key => $item) {
      if ($item instanceof Element) {
        $result[] = $path . $key . '=' . $item->getId();
      } elseif (is_string($item) || is_numeric($item)) {
        $result[] = $path . $key . '=' . (string)$item;
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
   * @return string
   */
  static public function getUrlCacheKey() {
    try {
      return 'url:' . Craft::$app->request->getUrl();
    } catch (Throwable $error) {
      return null;
    }
  }

  /**
   * @return string
   */
  static public function getRouteCacheKey() {
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
