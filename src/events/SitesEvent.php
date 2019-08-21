<?php

namespace lenz\craft\essentials\events;

use craft\models\Site;
use yii\base\Event;

/**
 * Class SitesEvent
 */
class SitesEvent extends Event
{
  /**
   * @var Site[]
   */
  public $sites;
}
