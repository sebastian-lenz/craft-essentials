<?php

namespace lenz\craft\events;

use craft\base\Element;
use craft\web\Request;
use lenz\craft\Plugin;
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

  /**
   * @return bool
   */
  private function generateCacheKey() {
    $request = \Craft::$app->request;
    if (
      !($request instanceof Request) ||
      !$request->getIsGet() ||
      $request->getIsCpRequest() ||
      \Craft::$app->getConfig()->getGeneral()->devMode
    ) {
      return true;
    }

    $route = \Craft::$app->requestedRoute;
    if (!in_array($route, Plugin::getInstance()->getSettings()->cachedRoutes)) {
      return true;
    }

    $params = \Craft::$app->getUrlManager()->getRouteParams();
    if (!is_array($params)) {
      return true;
    }

    $params += $request->getQueryParams();
    $parts = self::flatten($params);
    $this->cacheKey = implode(';', $parts);

    return false;
  }

  /**
   * @param array $items
   * @param string $path
   * @return array|false
   */
  static private function flatten($items, $path = '') {
    $result = array();

    foreach ($items as $key => $item) {
      if ($item instanceof Element) {
        $result[] = $path . $key . '=' . $item->getId();
      } elseif (is_string($item) || is_numeric($item)) {
        $result[] = $path . $key . '=' . (string)$item;
      } elseif (is_array($item)) {
        $flattened = self::flatten($item, $path . $key . '.');
        if ($flattened === false) return false;
        $result = array_merge($result, $flattened);
      } else {
        return false;
      }
    }

    sort($result);
    return $result;
  }
}
