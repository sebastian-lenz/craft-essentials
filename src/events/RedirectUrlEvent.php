<?php

namespace lenz\craft\essentials\events;

use yii\base\Event;

/**
 * Class RedirectUrlEvent
 */
class RedirectUrlEvent extends Event
{
  /**
   * @var string
   */
  public string $requestUrl;

  /**
   * @var string[]
   */
  public array $urls = [];
}
