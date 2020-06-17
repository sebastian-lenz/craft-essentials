<?php

namespace lenz\craft\essentials\services\siteMap;

use lenz\craft\essentials\services\siteMap\sources\AbstractSource;
use lenz\craft\essentials\services\siteMap\sources\EntrySource;
use yii\base\Component;

/**
 * Class SiteMap
 */
class SiteMapService extends Component
{
  /**
   * @var SiteMapService
   */
  static private $_instance;

  /**
   * @var string
   */
  const EVENT_ENTRY_QUERY = 'siteMapEntryQuery';


  /**
   * SiteMap constructor.
   * @param array $config
   */
  public function __construct($config = []) {
    parent::__construct($config);
  }

  /**
   * @return SiteMap
   */
  public function create() {
    $siteMap = new SiteMap();

    foreach ($this->getSources() as $source) {
      $source->collect($siteMap);
    }

    return $siteMap;
  }

  /**
   * @return AbstractSource[]
   */
  public function getSources() {
    return [
      new EntrySource(),
    ];
  }


  // Static methods
  // --------------

  /**
   * @return SiteMapService
   */
  public static function getInstance() {
    if (!isset(self::$_instance)) {
      self::$_instance = new SiteMapService();
    }

    return self::$_instance;
  }
}
