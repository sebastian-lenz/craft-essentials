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

/**
 * Class FrontendCache
 */
class FrontendCache extends Component
{
  /**
   * @var string
   */
  private $cacheKey = null;

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

    Event::on(Application::class, Application::EVENT_BEFORE_ACTION, [$this, 'onBeforeAction']);
    Event::on(Application::class, Application::EVENT_AFTER_REQUEST, [$this, 'onAfterRequest']);
  }

  /**
   * @param ActionEvent $event
   */
  public function onBeforeAction(ActionEvent $event) {
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

    $cache = ElementCache::getCache();
    $cacheKey = self::class . ';cacheKey=' . $cacheKeyEvent->cacheKey;
    $cacheData = $cache->get($cacheKey);

    if ($cacheData !== false) {
      $response = Craft::$app->response;
      $response->headers->fromArray($cacheData['headers']);
      $response->data = $cacheData['data'];
      $response->format = $cacheData['format'];

      $event->handled = true;
      $event->isValid = false;
    } else {
      $this->cacheKey = $cacheKey;
    }
  }

  /**
   * @return void
   */
  public function onAfterRequest() {
    if (is_null($this->cacheKey)) {
      return;
    }

    $response = Craft::$app->response;
    if (
      $response->statusCode != 200 ||
      strpos($response->data, Craft::$app->getConfig()->general->csrfTokenName) !== false ||
      strpos($response->data, 'actions/assets/generate-transform') !== false
    ) {
      return;
    }

    $durationEvent = new CacheDurationEvent();
    $this->trigger(self::EVENT_CACHE_DURATION, $durationEvent);
    if ($durationEvent->handled) {
      return;
    }

    $cache = ElementCache::getCache();
    $cache->set($this->cacheKey, array(
      'data'    => $response->data,
      'format'  => $response->format,
      'headers' => $response->headers->toArray(),
    ), $durationEvent->duration);
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
