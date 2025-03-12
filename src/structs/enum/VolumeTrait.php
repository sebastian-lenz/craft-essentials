<?php

namespace lenz\craft\essentials\structs\enum;

use Craft;
use craft\models\Volume;
use Throwable;

/**
 * Trait VolumeTrait
 *
 * @noinspection PhpUnused
 */
trait VolumeTrait
{
  /**
   * @return int
   */
  public function getId(): int {
    return $this->getModel()->getId();
  }

  /**
   * @return Volume
   * @noinspection PhpUndefinedFieldInspection
   */
  public function getModel(): Volume {
    return Craft::$app->getVolumes()->getVolumeByHandle($this->value);
  }

  /**
   * @param mixed $value
   * @return bool
   * @phpstan-assert-if-true Volume $value
   * @noinspection PhpUndefinedFieldInspection
   */
  public function is(mixed $value): bool {
    try {
      return (
        $value instanceof Volume &&
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
   * @param SectionTrait[] $volumes
   * @return bool
   * @phpstan-assert-if-true Volume $value
   */
  static public function isAny(mixed $value, array $volumes): bool {
    foreach ($volumes as $volume) {
      if ($volume->is($value)) {
        return true;
      }
    }

    return false;
  }
}
