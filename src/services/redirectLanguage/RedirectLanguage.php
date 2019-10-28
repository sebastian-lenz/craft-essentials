<?php

namespace lenz\craft\essentials\services\redirectLanguage;

use Craft;
use craft\web\Application;
use Exception;
use lenz\craft\essentials\Plugin;
use Throwable;
use yii\base\Component;
use yii\base\Event;

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
   * @param Event $event
   */
  public function onApplicationInit(Event $event) {
    $request = Craft::$app->getRequest();
    $enabled = Plugin::getInstance()
      ->getSettings()
      ->enableLanguageRedirect;

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
  }

  /**
   * Return the best matching language.
   *
   * @return string
   * @throws Exception
   */
  public function getBestLanguage() {
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
  public function getBestSiteUrl() {
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
  public function getLanguageStack() {
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
  public static function getInstance() {
    if (!isset(self::$_instance)) {
      self::$_instance = new RedirectLanguage();
    }

    return self::$_instance;
  }
}
