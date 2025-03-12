<?php

namespace lenz\craft\essentials\structs\enum;

use Craft;
use craft\models\Site;
use Throwable;

/**
 * Trait SiteTrait
 *
 * @noinspection PhpUnused
 */
trait SiteTrait
{
  /**
   * @return int
   */
  public function getId(): int {
    return $this->getModel()->getId();
  }

  /**
   * @return Site
   * @noinspection PhpUndefinedFieldInspection
   */
  public function getModel(): Site {
    return Craft::$app->getSites()->getSiteByHandle($this->value);
  }

  /**
   * @param mixed $value
   * @return bool
   * @phpstan-assert-if-true Site $value
   * @noinspection PhpUndefinedFieldInspection
   */
  public function is(mixed $value): bool {
    try {
      return (
        $value instanceof Site &&
        $value->handle == $this->value
      );
    } catch (Throwable) {
      return false;
    }
  }


  // Static methods
  // --------------

  /**
   * @param mixed $value
   * @param SiteTrait[] $sites
   * @return bool
   * @phpstan-assert-if-true Site $value
   */
  static public function isAny(mixed $value, array $sites): bool {
    foreach ($sites as $site) {
      if ($site->is($value)) {
        return true;
      }
    }

    return false;
  }
}
