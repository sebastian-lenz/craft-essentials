<?php

namespace lenz\craft\essentials\services\tables;

/**
 * Class Filter
 */
readonly class Filter
{
  /**
   * @param mixed $callback
   * @param FilterType $type
   */
  public function __construct(
    public mixed $callback,
    public FilterType $type,
  ) { }

  /**
   * @param mixed $value
   * @return mixed
   */
  public function __invoke(mixed $value): mixed {
    return ($this->callback)($value);
  }
}
