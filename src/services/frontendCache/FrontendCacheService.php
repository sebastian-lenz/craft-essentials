<?php

namespace lenz\craft\essentials\services\frontendCache;

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
 * Class FrontendCacheService
 */
class FrontendCacheService extends Component
{
  /**
   * @var string
   */
  private $_cacheKey = null;

  /**
   * @var FrontendCacheService
   */
  static private $_instance;

  /**
   * Placeholder used to represent CSRF tokens when caching pages.
   */
  const CSRF_PLACEHOLDER = '<!-- ($DRY_CSRF_TOKEN); -->';

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

    Event::on(
      Application::class,
      Application::EVENT_INIT,
      [$this, 'onApplicationInit']
    );
  }

  /**
   * @return void
   */
  public function onAfterRequest() {
    if (is_null($this->_cacheKey)) {
      return;
    }

    $response = Craft::$app->response;
    if (
      $response->statusCode != 200 ||
      strpos($response->data, 'actions/assets/generate-transform') !== false
    ) {
      return;
    }

    $durationEvent = new CacheDurationEvent();
    $this->trigger(self::EVENT_CACHE_DURATION, $durationEvent);
    if ($durationEvent->handled) {
      return;
    }

    $key = $this->_cacheKey;
    $duration = $durationEvent->duration;
    $this->setCacheData($key, $response, $duration);
  }

  /**
   * @return void
   */
  public function onApplicationInit() {
    if (!$this->isEnabled()) {
      return;
    }

    Event::on(
      Application::class,
      Application::EVENT_BEFORE_ACTION,
      [$this, 'onBeforeAction']
    );
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

    $response = $this->getCachedResponse($cacheKeyEvent->cacheKey);
    if (is_null($response)) {
      $this->_cacheKey = $cacheKeyEvent->cacheKey;
      Event::on(
        Application::class,
        Application::EVENT_AFTER_REQUEST,
        [$this, 'onAfterRequest']
      );
    } else {
      $event->handled = true;
      $event->isValid = false;
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

    $data = $cacheData['data'];
    if (strpos($data, self::CSRF_PLACEHOLDER) !== false) {
      $token = Craft::$app->getRequest()->getCsrfToken();
      $data = str_replace(self::CSRF_PLACEHOLDER, $token, $data);
    }

    $response = Craft::$app->response;
    $response->headers->fromArray($cacheData['headers']);
    $response->headers->add('X-Craft-Cache', 'hit');
    $response->data = $data;
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
   * @param string $key
   * @param Response $response
   * @param int $duration
   */
  protected function setCacheData(string $key, Response $response, int $duration) {
    $cache     = ElementCache::getCache();
    $tokenName = Craft::$app->getConfig()->general->csrfTokenName;
    $key       = $this->getCacheKey($key);
    $data      = $response->data;

    if (strpos($data, $tokenName) !== false) {
      $token = Craft::$app->getRequest()->getCsrfToken();
      $data = str_replace($token, self::CSRF_PLACEHOLDER, $data);
    }

    $cache->set($key, array(
      'data'    => $data,
      'format'  => $response->format,
      'headers' => $response->headers->toArray(),
    ), $duration);
  }


  // Static methods
  // --------------

  /**
   * @return FrontendCacheService
   */
  public static function getInstance() {
    if (!isset(self::$_instance)) {
      self::$_instance = new FrontendCacheService();
    }

    return self::$_instance;
  }
}
