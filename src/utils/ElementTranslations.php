<?php

namespace lenz\craft\essentials\utils;

use Craft;
use craft\base\ElementInterface;
use craft\elements\Entry;
use craft\errors\SiteNotFoundException;
use craft\models\Site;
use lenz\craft\essentials\Plugin;
use Solspace\Calendar\Elements\Event;
use yii\base\BaseObject;

/**
 * Class ElementTranslations
 */
class ElementTranslations extends BaseObject
{
  /**
   * @var ElementInterface|null
   */
  private $_element;


  /**
   * @return ElementInterface[]
   */
  public function getAllTranslations() {
    $element = $this->getElement();

    if ($element instanceof Entry) {
      return $this->getEntryTranslations($element);
    } elseif ($element instanceof Event) {
      return $this->getEventTranslations($element);
    }

    return [];
  }

  /**
   * @return ElementInterface
   */
  public function getElement() {
    return isset($this->_element)
      ? $this->_element
      : Craft::$app->getUrlManager()->getMatchedElement();
  }

  /**
   * @return Site[]
   */
  public function getSites() {
    return Plugin::getInstance()->languageRedirect->getEnabledSites();
  }

  /**
   * @return array
   */
  public function getTranslations() {
    $sites        = $this->getSites();
    $translations = $this->getAllTranslations();

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
        'isCurrent' => $currentSite === $site,
        'name'      => $site->name,
        'site'      => $site,
        'target'    => $target,
        'url'       => is_null($target) ? $site->baseUrl : $target->getUrl(),
      ];
    }, $sites);
  }

  /**
   * @param ElementInterface|null $value
   * @return $this
   */
  public function setElement(ElementInterface $value = null) {
    $this->_element = $value;
    return $this;
  }


  // Private methods
  // ---------------

  /**
   * @param Entry $entry
   * @return Entry[]
   */
  private function getEntryTranslations(Entry $entry) {
    return Entry::findAll([
      'id'            => $entry->id,
      'site'          => '*',
      'withStructure' => false,
    ]);
  }

  /**
   * @param Event $event
   * @return Event[]
   */
  private function getEventTranslations(Event $event) {
    return array_filter(array_map(function(Site $site) use ($event) {
      if ($event->siteId == $site->id) {
        return $event;
      }

      return Event::findOne([
        'id'   => $event->id,
        'site' => $site,
      ]);
    }, $this->getSites()));
  }


  // Static methods
  // --------------

  /**
   * @param array $options
   * @return ElementTranslations
   */
  static function create(array $options = []) {
    return new ElementTranslations($options);
  }
}
