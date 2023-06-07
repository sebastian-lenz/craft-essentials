<?php

namespace lenz\craft\essentials\services\siteMap\sources;

use Craft;
use craft\base\Element;
use craft\models\Site;
use lenz\craft\essentials\events\SitesEvent;
use lenz\craft\essentials\Plugin;
use lenz\craft\essentials\services\siteMap\SiteMap;
use yii\base\Event;
use yii\base\InvalidConfigException;

/**
 * Class AbstractElementSource
 */
abstract class AbstractElementSource extends AbstractSource
{
  /**
   * @var string
   */
  const EVENT_SOURCE_SITES = 'findSourceSites';


  /**
   * @param SiteMap $siteMap
   * @throws InvalidConfigException
   */
  public function collect(SiteMap $siteMap): void {
    $groups = $this->toGroups($this->getElements());

    foreach ($groups as $group) {
      $this->addGroup($siteMap, $group);
    }
  }


  // Abstract methods
  // ----------------

  /**
   * @return Element[]
   */
  abstract protected function getElements(): array;


  // Protected methods
  // -----------------

  /**
   * @param SiteMap $siteMap
   * @param array $group
   */
  protected function addGroup(SiteMap $siteMap, array $group): void {
    $links = [];
    foreach ($group as $language => $item) {
      $links[] = SiteMap::xmlLink([
        'href'     => $item['url'],
        'hreflang' => $language,
        'rel'      => 'alternate',
      ]);
    }

    $links = count($links) > 1 ? implode('', $links) : null;
    foreach ($group as $item) {
      $siteMap->addUrl($item['url'], $item['lastmod'], $links);
    }
  }

  /**
   * @return string[]
   */
  protected function getQuerySites(): array {
    return array_map(
      fn(Site $site) => $site->handle,
      SitesEvent::findSites($this, self::EVENT_SOURCE_SITES)
    );
  }

  /**
   * @param Element[] $elements
   * @return array
   * @throws InvalidConfigException
   */
  protected function toGroups(array $elements): array {
    $groups = [];

    foreach ($elements as $element) {
      $url = $element->getUrl();
      if (is_null($url)) {
        continue;
      }

      $site = $element->getSite();
      $groups[$element->id][$site->language] = [
        'url'     => $url,
        'lastmod' => $element->dateUpdated,
      ];
    }

    return $groups;
  }
}
