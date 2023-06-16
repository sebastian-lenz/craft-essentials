<?php

namespace lenz\craft\essentials\services\redirectLanguage;

use Craft;
use craft\controllers\TemplatesController;
use craft\models\Site;
use craft\web\Application;
use craft\web\Request;
use craft\web\Response;
use Exception;
use lenz\craft\essentials\events\SitesEvent;
use lenz\craft\essentials\Plugin;
use Throwable;
use yii\base\ActionEvent;
use yii\base\Component;
use yii\base\Event;
use yii\base\InlineAction;
use yii\base\Module;

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
   * Languages constructor.
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
  public function onApplicationInit(): void {
    $request = Craft::$app->getRequest();
    $settings = Plugin::getInstance()->getSettings();
    $enabled = $settings->enableLanguageRedirect;

    if ($enabled && self::isIndexRequest($request)) {
      $url = $this->getBestSiteUrl();
      if (!is_null($url)) {
        Craft::$app->getResponse()
          ->redirect($url)
          ->send();

        exit;
      }
    }

    if ($settings->ensureSiteSegment && $request->isSiteRequest) {
      Craft::$app->on(Module::EVENT_BEFORE_ACTION, function(ActionEvent $event) {
        if (
          !($event->action instanceof InlineAction) ||
          !($event->action->controller instanceof TemplatesController) ||
          $event->action->id !== 'render' ||
          !Craft::$app->urlManager->getMatchedElement()
        ) {
          return;
        }

        $fullUrl = self::ensureSiteBaseUrl();
        if (!is_null($fullUrl)) {
          $response = new Response();
          $response->redirect($fullUrl, 301)->send();
          die();
        }
      });
    }
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
      $uri = Craft::$app->request->getFullUri();
    }

    $baseUrl = Craft::$app->sites->currentSite->getBaseUrl();
    $baseSegment = trim($baseUrl, '/');
    if (str_starts_with($baseSegment, 'http')) {
      $baseSegment = substr($baseSegment, strpos($baseSegment, '/', 8) + 1);
    }

    return !str_starts_with($uri, $baseSegment)
      ? $baseUrl . $uri
      : null;
  }

  /**
   * @param \yii\base\Request $request
   * @return bool
   */
  public static function isIndexRequest(\yii\base\Request $request): bool {
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
}
