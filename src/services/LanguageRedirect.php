<?php

namespace lenz\craft\essentials\services;

use Craft;
use craft\models\Site;
use Exception;
use lenz\craft\essentials\events\SitesEvent;
use lenz\craft\essentials\Plugin;
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
   * @var Site[]
   */
  private $_enabledSites;

  /**
   * @var LanguageStack
   */
  private $_languageStack;

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
      count($request->queryParams) == 0 &&
      Plugin::getInstance()->getSettings()->enableLanguageRedirect
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

    $site = $this->getEnabledSite($language);
    return is_null($site)
      ? null
      : $site->getBaseUrl();
  }

  /**
   * @param string $language
   * @return Site|null
   */
  public function getEnabledSite($language) {
    foreach ($this->getEnabledSites() as $site) {
      if ($site->language == $language) {
        return $site;
      }
    }

    return null;
  }

  /**
   * @return Site[]
   */
  public function getEnabledSites() {
    if (!isset($this->_enabledSites)) {
      $settings = Plugin::getInstance()->getSettings();
      $disabledLanguages = $settings->disabledLanguages;

      $sites = array_filter(
        Craft::$app->getSites()->getAllSites(),
        function(Site $site) use ($disabledLanguages) {
          return !in_array($site->language, $disabledLanguages);
        }
      );

      if ($this->hasEventHandlers(self::EVENT_AVAILABLE_SITES)) {
        $event = new SitesEvent(['sites' => $sites]);
        $this->trigger(self::EVENT_AVAILABLE_SITES, $event);
        $sites = $event->sites;
      }

      $this->_enabledSites = $sites;
    }

    return $this->_enabledSites;
  }

  /**
   * @return LanguageStack
   */
  public function getLanguageStack() {
    if (!isset($this->_languageStack)) {
      $stack = new LanguageStack();
      foreach ($this->getEnabledSites() as $site) {
        $stack->addLanguage($site->language);
      }

      $this->_languageStack = $stack;
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
