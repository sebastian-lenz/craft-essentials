<?php

namespace lenz\craft\essentials\services\siteMap\sources;

use lenz\craft\essentials\services\siteMap\SiteMap;

/**
 * Class AbstractSource
 */
abstract class AbstractSource
{
  /**
   * @param SiteMap $siteMap
   */
  abstract public function collect(SiteMap $siteMap): void;
}
