<?php

namespace lenz\craft\essentials\services;

use Craft;
use craft\web\Application;
use lenz\craft\essentials\events\CacheDurationEvent;
use lenz\craft\essentials\events\CacheKeyEvent;
use lenz\craft\utils\elementCache\ElementCache;
use yii\base\ActionEvent;
use yii\base\Component;
use yii\base\Event;
use yii\base\Response;

/**
 * Class FrontendCache
 */
class FrontendCache extends Component
{
  /**
   * @var string
   */
  private $_cacheKey = null;

  /**
   * @var FrontendCache
   */
  static private $_instance;

  /**
   * Triggered when the plugin is looking for the default cache duration.
   */
  const EVENT_DEFAULT_CACHE_DURATION = 'defaultCacheDuration';

  /**
   * Triggered when the plugin is looking for the cache duration
   * for the current request.
   */
  const EVENT_CACHE_DURATION = 'cacheDuration';

  /**
   * Triggered when the plugin is looking for a cache key.
   */
  const EVENT_CACHE_KEY = 'cacheKey';


  /**
   * FrontendCache constructor.
   */
  public function __construct() {
    parent::__construct();

    if ($this->isEnabled()) {
      Event::on(
        Application::class, Application::EVENT_BEFORE_ACTION,
        function(ActionEvent $event) {
          $this->onBeforeAction($event);
        }
      );

      Event::on(
        Application::class, Application::EVENT_AFTER_REQUEST,
        function() {
          $this->onAfterRequest();
        }
      );
    }
  }


  // Protected methods
  // -----------------

  /**
   * @param string $key
   * @return Response|null
   */
  protected function getCachedResponse(string $key) {
    $cacheData = $this->getCacheData($key);
    if ($cacheData === false) {
      return null;
    }

    $response = Craft::$app->response;
    $response->headers->fromArray($cacheData['headers']);
    $response->headers->add('X-Craft-Cache', 'hit');
    $response->data = $cacheData['data'];
    $response->format = $cacheData['format'];

    return $response;
  }

  /**
   * @param string $key
   * @return mixed
   */
  protected function getCacheData(string $key) {
    $cache = ElementCache::getCache();
    $key   = $this->getCacheKey($key);

    return $cache->get($key);
  }

  /**
   * @param string $key
   * @return string
   */
  protected function getCacheKey(string $key) : string {
    return self::class . ';cacheKey=' . $key;
  }

  /**
   * @return bool
   */
  protected function isEnabled() {
    $request = Craft::$app->getRequest();
    if (
      $request->isCpRequest ||
      $request->isConsoleRequest ||
      Craft::$app->getConfig()->getGeneral()->devMode ||
      !Craft::$app->getUser()->getIsGuest() ||
      !$request->isGet
    ) {
      return false;
    }

    $key = CacheKeyEvent::getUrlCacheKey();
    $response = $this->getCachedResponse($key);
    if (!is_null($response)) {
      $response->send();
      exit();
    }

    return true;
  }

  /**
   * @param ActionEvent $event
   */
  protected function onBeforeAction(ActionEvent $event) {
    if (
      $event->action->id != 'render' ||
      $event->action->controller->id != 'templates'
    ) {
      return;
    }

    $cacheKeyEvent = new CacheKeyEvent();
    $this->trigger(self::EVENT_CACHE_KEY, $cacheKeyEvent);
    if ($cacheKeyEvent->handled || is_null($cacheKeyEvent->cacheKey)) {
      return;
    }

    $response = $this->getCachedResponse($cacheKeyEvent->cacheKey);
    if (is_null($response)) {
      $this->_cacheKey = $cacheKeyEvent->cacheKey;
    } else {
      $event->handled = true;
      $event->isValid = false;
    }
  }

  /**
   * @return void
   */
  protected function onAfterRequest() {
    if (is_null($this->_cacheKey)) {
      return;
    }

    $response      = Craft::$app->response;
    $csrfTokenName = Craft::$app->getConfig()->general->csrfTokenName;
    if (
      $response->statusCode != 200 ||
      strpos($response->data, $csrfTokenName) !== false ||
      strpos($response->data, 'actions/assets/generate-transform') !== false
    ) {
      return;
    }

    $durationEvent = new CacheDurationEvent();
    $this->trigger(self::EVENT_CACHE_DURATION, $durationEvent);
    if ($durationEvent->handled) {
      return;
    }

    $key      = $this->_cacheKey;
    $duration = $durationEvent->duration;
    $this->setCacheData($key, $response, $duration);
  }

  /**
   * @param string $key
   * @param Response $response
   * @param int $duration
   */
  protected function setCacheData(string $key, Response $response, int $duration) {
    $cache = ElementCache::getCache();
    $key   = $this->getCacheKey($key);

    $cache->set($key, array(
      'data'    => $response->data,
      'format'  => $response->format,
      'headers' => $response->headers->toArray(),
    ), $duration);
  }


  // Static methods
  // --------------

  /**
   * @return FrontendCache
   */
  public static function getInstance() {
    if (!isset(self::$_instance)) {
      self::$_instance = new FrontendCache();
    }

    return self::$_instance;
  }
}
