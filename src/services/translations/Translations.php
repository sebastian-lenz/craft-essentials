<?php

namespace lenz\craft\essentials\services\translations;

use Craft;
use craft\base\ElementInterface;
use craft\errors\SiteNotFoundException;
use craft\models\Site;
use lenz\craft\essentials\events\SitesEvent;
use lenz\craft\essentials\Plugin;
use lenz\craft\essentials\services\translations\loaders\AbstractLoader;
use yii\base\Component;

/**
 * Class ElementTranslations
 */
class Translations extends Component
{
  /**
   * @var Site[]
   */
  private $_enabledSites;

  /**
   * Event triggered when looking for available sites.
   */
  const EVENT_AVAILABLE_SITES = 'availableSites';


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
   * @param ElementInterface|null $element
   * @param array $options
   * @return array
   */
  public function getTranslations($element = null, array $options = []) {
    $sites = $this->getEnabledSites();
    $translations = $element instanceof ElementInterface
      ? $this->loadTranslations($element)
      : [];

    try {
      $currentSite = Craft::$app->getSites()->getCurrentSite();
    } catch (SiteNotFoundException $exception) {
      $currentSite = null;
    }

    return array_map(function(Site $site) use ($currentSite, $translations) {
      $target = null;
      foreach ($translations as $translation) {
        if ($translation->siteId == $site->id) {
          $target = $translation;
          break;
        }
      }

      return [
        'isCurrent'  => $currentSite === $site,
        'isFallback' => is_null($target),
        'name'       => $site->name,
        'site'       => $site,
        'target'     => $target,
        'url'        => is_null($target) ? $site->baseUrl : $target->getUrl(),
      ];
    }, $sites);
  }

  /**
   * @param ElementInterface $element
   * @return ElementInterface[]
   */
  public function loadTranslations(ElementInterface $element) {
    foreach (AbstractLoader::getLoaders() as $loader) {
      $result = $loader->load($element);
      if ($result !== false) {
        return $result;
      }
    }

    return [];
  }
}
