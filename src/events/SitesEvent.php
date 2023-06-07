<?php

namespace lenz\craft\essentials\events;

use Craft;
use craft\models\Site;
use lenz\craft\essentials\Plugin;
use yii\base\Event;

/**
 * Class SitesEvent
 */
class SitesEvent extends Event
{
  /**
   * @var Site[]
   */
  public array $sites;


  // Static methods
  // --------------

  /**
   * @param object $scope
   * @param string $name
   * @return Site[]
   */
  static function findSites(object $scope, string $name): array {
    $event = self::forEnabledSites();
    Event::trigger($scope, $name, $event);

    return $event->sites;
  }

  /**
   * @return SitesEvent
   */
  static public function forEnabledSites(): SitesEvent {
    $settings = Plugin::getInstance()->getSettings();
    $disabledLanguages = $settings->disabledLanguages;

    return new SitesEvent([
      'sites' => array_filter(
        Craft::$app->getSites()->getAllSites(),
        fn(Site $site) => $site->enabled && !in_array($site->language, $disabledLanguages)
      ),
    ]);
  }
}
