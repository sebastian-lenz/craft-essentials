<?php

namespace lenz\craft\essentials\services\redirectLanguage;

use Craft;
use craft\controllers\TemplatesController;
use craft\web\Application;
use craft\web\Response;
use Exception;
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
  private $_languageStack;

  /**
   * @var RedirectLanguage
   */
  static private $_instance;


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
  public function onApplicationInit() {
    $request = Craft::$app->getRequest();
    $settings = Plugin::getInstance()->getSettings();
    $enabled = $settings->enableLanguageRedirect;

    if (
      $enabled &&
      $request->isSiteRequest &&
      count($request->queryParams) == 0
    ) {
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
        echo '';
        if (
          !($event->action instanceof InlineAction) ||
          !($event->action->controller instanceof TemplatesController) ||
          $event->action->id !== 'render'
        ) {
          return;
        }

        $uri = Craft::$app->request->getFullUri();
        $baseUrl = Craft::$app->sites->currentSite->getBaseUrl();
        $baseSegment = trim($baseUrl, '/');
        if (str_starts_with($baseSegment, 'http')) {
          $baseSegment = parse_url($baseSegment, strpos($baseSegment, '/', 8) + 1);
        }

        if (!str_starts_with($uri, $baseSegment)) {
          $response = new Response();
          $response->redirect($baseUrl . $uri, 301)->send();
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
   * @return string|null
   */
  public function getBestSiteUrl(): ?string {
    try {
      $language = $this->getBestLanguage();
    } catch (Throwable $error) {
      Craft::error($error->getMessage());
      return null;
    }

    $site = Plugin::getInstance()->translations->getEnabledSite($language);
    return is_null($site)
      ? null
      : $site->getBaseUrl();
  }

  /**
   * @return LanguageStack
   */
  public function getLanguageStack(): LanguageStack {
    if (!isset($this->_languageStack)) {
      $stack = new LanguageStack();
      $sites = Plugin::getInstance()->translations->getEnabledSites();

      foreach ($sites as $site) {
        $stack->addLanguage($site->language);
      }

      $this->_languageStack = $stack;
    }

    return $this->_languageStack;
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
}
