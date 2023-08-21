<?php

namespace lenz\craft\essentials\services\redirectLanguage;

use Craft;
use craft\controllers\TemplatesController;
use craft\models\Site;
use craft\web\Application;
use craft\web\Request;
use craft\web\Response;
use Exception;
use lenz\craft\essentials\events\RedirectEvent;
use lenz\craft\essentials\events\SitesEvent;
use lenz\craft\essentials\Plugin;
use Throwable;
use yii\base\Action;
use yii\base\ActionEvent;
use yii\base\Component;
use yii\base\Event;
use yii\base\InlineAction;
use yii\base\Module;
use yii\base\Request as BaseRequest;

/**
 * Class RedirectLanguage
 *
 * @see http://stackoverflow.com/questions/3770513/detect-browser-language-in-php
 */
class RedirectLanguage extends Component
{
  /**
   * @var LanguageStack
   */
  private LanguageStack $_languageStack;

  /**
   * @var RedirectLanguage
   */
  static private RedirectLanguage $_instance;

  /**
   * @var string
   */
  const EVENT_AVAILABLE_SITES = 'availableSites';

  /**
   * @var string
   */
  const EVENT_LANGUAGE_REDIRECT = 'languageRedirect';

  /**
   * @var string
   */
  const EVENT_SITE_SEGMENT_REDIRECT = 'siteSegmentRedirect';


  /**
   * Languages constructor.
   */
  public function __construct() {
    parent::__construct();
    Event::on(Application::class, Application::EVENT_INIT, $this->onApplicationInit(...));
  }

  /**
   * @return void
   */
  public function onApplicationInit(): void {
    $request = Craft::$app->getRequest();
    $settings = Plugin::getInstance()->getSettings();

    if ($settings->enableLanguageRedirect && self::isIndexRequest($request)) {
      $this->tryRedirect(self::EVENT_LANGUAGE_REDIRECT, $this->getBestSiteUrl());
    } elseif ($settings->ensureSiteSegment && $request->isSiteRequest) {
      Craft::$app->on(Module::EVENT_BEFORE_ACTION, $this->onBeforeAction(...));
    }
  }

  /**
   * @param ActionEvent $event
   * @return void
   */
  public function onBeforeAction(ActionEvent $event): void {
    $this->tryRedirect(
      self::EVENT_SITE_SEGMENT_REDIRECT,
      self::isPageAction($event->action) ? self::ensureSiteBaseUrl() : null,
      301
    );
  }

  /**
   * Return the best matching language.
   *
   * @return string
   * @throws Exception
   */
  public function getBestLanguage(): string {
    $stack = $this->getLanguageStack();
    if (count($stack) == 0) {
      throw new Exception('No available languages.');
    }

    if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
      return $stack->getBestGroup()->getBestLanguage();
    }

    return $stack->getBestLanguage(
      LanguageStack::fromString($_SERVER['HTTP_ACCEPT_LANGUAGE'])
    );
  }

  /**
   * @return Site|null
   */
  public function getBestSite(): ?Site {
    try {
      $language = $this->getBestLanguage();
    } catch (Throwable $error) {
      Craft::error($error->getMessage());
      return null;
    }

    return $this->getSiteByLanguage($language);
  }

  /**
   * @return string|null
   */
  public function getBestSiteUrl(): ?string {
    return $this->getBestSite()?->getBaseUrl();
  }

  /**
   * @return LanguageStack
   */
  public function getLanguageStack(): LanguageStack {
    if (!isset($this->_languageStack)) {
      $stack = new LanguageStack();
      foreach ($this->getSites() as $site) {
        $stack->addLanguage($site->language);
      }

      $this->_languageStack = $stack;
    }

    return $this->_languageStack;
  }


  // Private methods
  // ---------------

  /**
   * @param string $language
   * @return Site|null
   */
  private function getSiteByLanguage(string $language): ?Site {
    foreach ($this->getSites() as $site) {
      if ($site->language == $language) {
        return $site;
      }
    }

    return null;
  }

  /**
   * @return Site[]
   */
  private function getSites(): array {
    return SitesEvent::findSites($this, self::EVENT_AVAILABLE_SITES);
  }

  /**
   * @param string $eventName
   * @param string|null $url
   * @param int $statusCode
   * @return void
   */
  private function tryRedirect(string $eventName, ?string $url, int $statusCode = 302): void {
    if (empty($url)) {
      return;
    }

    $this->trigger($eventName, $event = new RedirectEvent([
      'statusCode' => $statusCode,
      'url' => $url,
    ]));

    if (!$event->handled) {
      $response = new Response();
      $response->redirect($event->url, $event->statusCode)->send();
      die();
    }
  }


  // Static methods
  // --------------

  /**
   * @return RedirectLanguage
   */
  public static function getInstance(): RedirectLanguage {
    if (!isset(self::$_instance)) {
      self::$_instance = new RedirectLanguage();
    }

    return self::$_instance;
  }

  /**
   * @param string|null $uri
   * @return string|null
   */
  public static function ensureSiteBaseUrl(?string $uri = null): ?string {
    if (is_null($uri)) {
      $uri = Craft::$app->getRequest()->getFullUri();
    }

    try {
      $siteUrl = Craft::$app->getSites()->getCurrentSite()->getBaseUrl();
    } catch (Throwable) {
      return null;
    }

    return self::hasSiteBaseUrl($uri, $siteUrl)
      ? null
      : $siteUrl . self::trimProtocolAndDomain($uri);
  }

  /**
   * @param string $uri
   * @param string|null $siteUrl
   * @return bool
   */
  public static function hasSiteBaseUrl(string $uri, ?string $siteUrl = null): bool {
    try {
      $siteUrl = $siteUrl ?? Craft::$app->getSites()->getCurrentSite()->getBaseUrl();
    } catch (Throwable) {
      return false;
    }

    return str_starts_with(
      self::trimProtocolAndDomain($uri),
      self::trimProtocolAndDomain($siteUrl)
    );
  }

  /**
   * @param BaseRequest $request
   * @return bool
   */
  public static function isIndexRequest(BaseRequest $request): bool {
    try {
      return (
        $request instanceof Request &&
        $request->isSiteRequest &&
        empty(trim($request->getPathInfo(true), '/'))
      );
    } catch (Throwable) {
      return false;
    }
  }

  /**
   * @param Action $action
   * @return bool
   */
  public static function isPageAction(Action $action): bool {
    return (
      $action instanceof InlineAction &&
      $action->controller instanceof TemplatesController &&
      $action->id === 'render' &&
      Craft::$app->urlManager->getMatchedElement()
    );
  }

  /**
   * @param string $uri
   * @return string
   */
  public static function trimProtocolAndDomain(string $uri): string {
    $path = trim($uri, '/');
    if (str_starts_with($path, 'http')) {
      $path = substr($path, strpos($path, '/', 8) + 1);
    }

    return $path;
  }
}
