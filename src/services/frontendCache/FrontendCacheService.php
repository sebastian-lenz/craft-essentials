<?php

namespace lenz\craft\essentials\services\frontendCache;

use Craft;
use craft\web\Application;
use lenz\craft\essentials\events\CacheDurationEvent;
use lenz\craft\essentials\events\CacheKeyEvent;
use lenz\craft\utils\elementCache\ElementCache;
use yii\base\ActionEvent;
use yii\base\Application as YiiApplication;
use yii\base\Component;
use yii\base\Event;
use yii\base\Module;
use yii\base\Response;

/**
 * Class FrontendCacheService
 */
class FrontendCacheService extends Component
{
  /**
   * @var string|null
   */
  private ?string $_cacheKey = null;

  /**
   * @var bool
   */
  private bool $_isIntercepted = false;

  /**
   * @var FrontendCacheService
   */
  static private FrontendCacheService $_instance;

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
   * Triggered when the plugin is looking for the default cache duration.
   */
  const EVENT_DEFAULT_CACHE_DURATION = 'defaultCacheDuration';


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
  public function intercept(): void {
    $this->_isIntercepted = true;
  }

  /**
   * @return void
   */
  public function onAfterRequest(): void {
    if (is_null($this->_cacheKey) || $this->_isIntercepted) {
      return;
    }

    $response = Craft::$app->response;
    if (
      $response->statusCode != 200 ||
      str_contains($response->data, 'actions/assets/generate-transform')
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
  public function onApplicationInit(): void {
    if (!$this->isEnabled()) {
      return;
    }

    Event::on(
      Application::class,
      Module::EVENT_BEFORE_ACTION,
      [$this, 'onBeforeAction']
    );
  }

  /**
   * @param ActionEvent $event
   */
  public function onBeforeAction(ActionEvent $event) {
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
        YiiApplication::EVENT_AFTER_REQUEST,
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
  protected function getCachedResponse(string $key): ?Response {
    $cacheData = $this->getCacheData($key);
    if ($cacheData === false) {
      return null;
    }

    $data = $cacheData['data'];
    if (str_contains($data, self::CSRF_PLACEHOLDER)) {
      $token = Craft::$app->getRequest()->getCsrfToken();
      $data = str_replace(self::CSRF_PLACEHOLDER, $token, $data);
    }

    if (
      array_key_exists('dataFormat', $cacheData) &&
      $cacheData['dataFormat'] == 'json'
    ) {
      $data = json_decode($data);
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
  protected function getCacheData(string $key): mixed {
    $cache = ElementCache::getCache();
    $key = $this->getCacheKey($key);

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
  protected function isEnabled(): bool {
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
    $data = $response->data;
    $dataFormat = 'raw';

    if (!is_string($data)) {
      $data = json_encode($data);
      $dataFormat = 'json';
    }

    $tokenName = Craft::$app->getConfig()
      ->general
      ->csrfTokenName;

    if (str_contains($data, $tokenName)) {
      $token = Craft::$app->getRequest()->getCsrfToken();
      $data = str_replace($token, self::CSRF_PLACEHOLDER, $data);
    }

    $cacheKey = $this->getCacheKey($key);
    $cacheData = [
      'data'       => $data,
      'dataFormat' => $dataFormat,
      'format'     => $response->format,
      'headers'    => $response->headers->toArray(),
    ];

    ElementCache::getCache()->set($cacheKey, $cacheData, $duration);
  }


  // Static methods
  // --------------

  /**
   * @return FrontendCacheService
   */
  public static function getInstance(): FrontendCacheService {
    if (!isset(self::$_instance)) {
      self::$_instance = new FrontendCacheService();
    }

    return self::$_instance;
  }
}
