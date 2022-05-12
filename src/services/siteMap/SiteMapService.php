<?php

namespace lenz\craft\essentials\services\siteMap;

use lenz\craft\essentials\services\siteMap\sources\AbstractSource;
use lenz\craft\essentials\services\siteMap\sources\EntrySource;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Class SiteMap
 */
class SiteMapService extends Component
{
  /**
   * @var SiteMapService
   */
  static private SiteMapService $_instance;

  /**
   * @var string
   */
  const EVENT_ENTRY_QUERY = 'siteMapEntryQuery';


  /**
   * @return SiteMap
   * @throws InvalidConfigException
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
    if (!isset(self::$_instance)) {
      self::$_instance = new SiteMapService();
    }

    return self::$_instance;
  }
}
