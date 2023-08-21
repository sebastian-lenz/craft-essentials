<?php

namespace lenz\craft\essentials\events;

use yii\base\Event;

/**
 * Class RedirectEvent
 */
class RedirectEvent extends Event
{
  /**
   * @var int
   */
  public int $statusCode = 301;

  /**
   * @var string
   */
  public string $url;
}
