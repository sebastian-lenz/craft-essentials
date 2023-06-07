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
  private array $_enabledSites;

  /**
   * Event triggered when looking for available sites.
   */
  const EVENT_AVAILABLE_SITES = 'availableSites';


  /**
   * @return Site[]
   */
  public function getEnabledSites(): array {
    if (!isset($this->_enabledSites)) {
      $this->_enabledSites = SitesEvent::findSites($this, self::EVENT_AVAILABLE_SITES);
    }

    return $this->_enabledSites;
  }

  /**
   * @param ElementInterface|null $element
   * @return Translation[]
   */
  public function getTranslations(ElementInterface $element = null): array {
    $sites = $this->getEnabledSites();
    $translations = $element instanceof ElementInterface
      ? $this->loadTranslations($element)
      : [];

    try {
      $currentSite = Craft::$app->getSites()->getCurrentSite();
    } catch (SiteNotFoundException) {
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

      return new Translation($site, [
        'isCurrent' => $currentSite === $site,
        'target' => $target,
      ]);
    }, $sites);
  }

  /**
   * @param ElementInterface $element
   * @return ElementInterface[]
   */
  public function loadTranslations(ElementInterface $element): array {
    foreach (AbstractLoader::getLoaders() as $loader) {
      $result = $loader->load($element);
      if ($result !== false) {
        return $result;
      }
    }

    return [];
  }


  // Static methods
  // --------------

  /**
   * @param mixed|null $element
   * @return Translation[]
   */
  static public function forElement(mixed $element = null): array {
    static $cache;
    if (!($element instanceof ElementInterface)) {
      $element = Craft::$app->getUrlManager()->getMatchedElement();
    }

    if ($element instanceof ElementInterface) {
      $id = $element->getId();
    } else {
      $element = null;
      $id = '*';
    }

    if (!isset($cache[$id])) {
      $cache[$id] = Plugin::getInstance()
        ->translations
        ->getTranslations($element);
    }

    return $cache[$id];
  }
}
