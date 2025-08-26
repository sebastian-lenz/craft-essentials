<?php

namespace lenz\craft\essentials\services\events;

use Attribute;

/**
 * Class Listener
 */
#[Attribute(Attribute::TARGET_CLASS| Attribute::TARGET_METHOD)]
readonly class On
{
  /**
   * @param string $class
   * @param string $name
   */
  public function __construct(
    public string $class,
    public string $name,
  ) { }
}
