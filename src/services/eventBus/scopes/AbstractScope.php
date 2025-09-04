<?php

namespace lenz\craft\essentials\services\eventBus\scopes;

use lenz\craft\essentials\services\eventBus\listeners\AbstractListener;

/**
 * Class AbstractScope
 */
abstract class AbstractScope
{
  /**
   * @return AbstractListener[]
   */
  abstract function findListeners(): array;
}
