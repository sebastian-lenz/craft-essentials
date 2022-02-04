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
  protected function getElements(): array {
    $query = Entry::find()
      ->site($this->getQuerySites())
      ->leftJoin('{{%lenz_seo}}', '{{%lenz_seo}}.elementId = {{%elements}}.id AND {{%lenz_seo}}.siteId = {{%elements_sites}}.siteId')
      ->andWhere('({{%lenz_seo}}.enabled IS NULL OR {{%lenz_seo}}.enabled = 1)');

    // Allow this query to be modified
    Plugin::getInstance()->siteMap->trigger(
      SiteMapService::EVENT_ENTRY_QUERY,
      new ElementQueryEvent([ 'query' => $query ])
    );

    return $query->all();
  }
}
