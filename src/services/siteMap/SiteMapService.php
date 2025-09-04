<?php

namespace lenz\craft\essentials\services\siteMap;

use lenz\craft\essentials\Plugin;
use lenz\craft\essentials\services\siteMap\sources\AbstractSource;
use lenz\craft\essentials\services\siteMap\sources\EntrySource;
use yii\base\Component;

/**
 * Class SiteMap
 */
class SiteMapService extends Component
{
  /**
   * @var string
   */
  const EVENT_ENTRY_QUERY = 'siteMapEntryQuery';


  /**
   * @return SiteMap
   */
  public function create(): SiteMap {
    $siteMap = new SiteMap();

    foreach ($this->getSources() as $source) {
      $source->collect($siteMap);
    }

    return $siteMap;
  }

  /**
   * @return AbstractSource[]
   */
  public function getSources(): array {
    return [
      new EntrySource(),
    ];
  }


  // Static methods
  // --------------

  /**
   * @return SiteMapService
   */
  public static function getInstance(): SiteMapService {
    return Plugin::getInstance()->siteMap;
  }
}
