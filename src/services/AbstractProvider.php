<?php

namespace lenz\craft\essentials\services;

/**
 * Class AbstractProvider
 */
abstract class AbstractProvider
{
  /**
   * @return void
   */
  abstract public static function register(): void;
}
