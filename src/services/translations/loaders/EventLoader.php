<?php

namespace lenz\craft\essentials\services\translations\loaders;

use craft\base\ElementInterface;
use craft\models\Site;
use lenz\craft\essentials\Plugin;
use Solspace\Calendar\Elements\Event;

/**
 * Class EventLoader
 */
class EventLoader extends AbstractLoader
{
  /**
   * @inheritDoc
   */
  public function load(ElementInterface $element) {
    if (!($element instanceof Event)) {
      return false;
    }

    $sites = Plugin::getInstance()->translations->getEnabledSites();

    return array_filter(array_map(function(Site $site) use ($element) {
      if ($element->siteId == $site->id) {
        return $element;
      }

      return Event::findOne([
        'id'   => $element->id,
        'site' => $site,
      ]);
    }, $sites));
  }
}
