<?php

namespace lenz\craft\essentials\services\eventBus;

use Attribute;

/**
 * Class Listener
 */
#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
readonly class On
{
  /**
   * @param string $class
   * @param string $name
   * @param mixed|null $when
   */
  public function __construct(
    public string $class,
    public string $name,
    public mixed $when = null
  ) { }

  /**
   * @param string $owner
   * @return bool
   */
  public function isEnabled(string $owner): bool {
    if (is_callable($this->when)) {
      return ($this->when)();
    } elseif (is_string($this->when)) {
      $callback = [$owner, $this->when];
      if (is_callable($callback)) {
        return $callback();
      }

      $callback = [$owner, 'getInstance'];
      $instance = is_callable($callback) ? $callback() : null;
      if ($instance && method_exists($instance, $this->when)) {
        return $instance->{$this->when}();
      }
    }

    return true;
  }
}
