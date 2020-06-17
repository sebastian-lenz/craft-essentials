<?php

namespace lenz\craft\essentials\services\siteMap\sources;

use craft\elements\Entry;
use craft\events\ElementQueryEvent;
use lenz\craft\essentials\Plugin;
use lenz\craft\essentials\services\siteMap\SiteMapService;

/**
 * Class EntrySource
 */
class EntrySource extends AbstractElementSource
{
  /**
   * @inheritDoc
   */
  protected function getElements() {
    $query = Entry::find()->site($this->getQuerySites());

    // Allow this query to be modified
    Plugin::getInstance()->siteMap->trigger(
      SiteMapService::EVENT_ENTRY_QUERY,
      new ElementQueryEvent([ 'query' => $query ])
    );

    return $query->all();
  }
}
