<?php

namespace lenz\craft\essentials\helpers;

/**
 * Class Arr
 */
class Arr extends \Illuminate\Support\Arr
{
  /**
   * @phpstan-param iterable $values
   * @phpstan-param callable $callback
   * @phpstan-return int|string|false
   */
  public static function findIndex(iterable $values, callable $callback): int|string|false {
    foreach ($values as $key => $value) {
      if ($callback($value, $key)) {
        return $key;
      }
    }

    return false;
  }
}
