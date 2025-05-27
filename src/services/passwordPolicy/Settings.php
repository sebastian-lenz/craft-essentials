<?php

namespace lenz\craft\essentials\services\passwordPolicy;

use craft\base\Model;

/**
 * Class Settings
 */
class Settings extends Model
{
  /**
   * @var bool
   */
  public bool $enabled = false;

  /**
   * @var int
   */
  public int $maxLength = 0;

  /**
   * @var int
   */
  public int $minLength = 6;

  /**
   * @var bool
   */
  public bool $requireCases = true;

  /**
   * @var bool
   */
  public bool $requireNumbers = true;

  /**
   * @var bool
   */
  public bool $requireSymbols = true;

  /**
   * @var bool
   */
  public bool $usePwnedValidator = true;
}
