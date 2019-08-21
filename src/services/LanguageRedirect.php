<?php

namespace lenz\craft\essentials\services;

use Craft;
use craft\models\Site;
use Exception;
use lenz\craft\essentials\events\SitesEvent;
use lenz\craft\essentials\utils\LanguageStack;
use Throwable;
use yii\base\Component;

/**
 * Class LanguageRedirect
 * @see http://stackoverflow.com/questions/3770513/detect-browser-language-in-php
 */
class LanguageRedirect extends Component
{
  /**
   * @var LanguageStack
   */
  private $_languageStack;

  /**
   * @var Site[]
   */
  private $_sites;

  /**
   * @var LanguageRedirect
   */
  static private $_instance;

  /**
   * Event triggered when looking for available sites.
   */
  const EVENT_AVAILABLE_SITES = 'availableSites';


  /**
   * Languages constructor.
   */
  public function __construct() {
    parent::__construct();

    $request = Craft::$app->getRequest();
    if (
      $request->isSiteRequest &&
      count($request->queryParams) == 0
    ) {
      $url = $this->getBestSiteUrl();
      if (!is_null($url)) {
        Craft::$app->getResponse()->redirect($url);
      }
    }
  }

  /**
   * Return the best matching language.
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

    return isset($this->_sites[$language])
      ? $this->_sites[$language]->getBaseUrl()
      : null;
  }

  /**
   * @return LanguageStack
   */
  public function getLanguageStack() {
    if (!isset($this->_languageStack)) {
      $this->_languageStack = new LanguageStack();
      $this->_sites = [];

      $sites = Craft::$app->getSites()->getAllSites();
      if ($this->hasEventHandlers(self::EVENT_AVAILABLE_SITES)) {
        $event = new SitesEvent(['sites' => $sites]);
        $this->trigger(self::EVENT_AVAILABLE_SITES, $event);
        $sites = $event->sites;
      }

      foreach ($sites as $site) {
        $language = $site->language;
        $this->_languageStack->addLanguage($language);
        $this->_sites[$language] = $site;
      }
    }

    return $this->_languageStack;
  }


  // Static methods
  // --------------

  /**
   * @return LanguageRedirect
   */
  public static function getInstance() {
    if (!isset(self::$_instance)) {
      self::$_instance = new LanguageRedirect();
    }

    return self::$_instance;
  }
}
