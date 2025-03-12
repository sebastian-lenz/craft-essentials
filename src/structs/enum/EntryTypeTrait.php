<?php

namespace lenz\craft\essentials\structs\enum;

use Craft;
use craft\elements\Entry;
use craft\models\EntryType;
use Throwable;

/**
 * Trait EntryTypeTrait
 *
 * @noinspection PhpUnused
 */
trait EntryTypeTrait
{
  /**
   * @return int
   */
  public function getId(): int {
    return $this->getModel()->getId();
  }

  /**
   * @return EntryType
   * @noinspection PhpUndefinedFieldInspection
   */
  public function getModel(): EntryType {
    return Craft::$app->getEntries()->getEntryTypeByHandle($this->value);
  }

  /**
   * @param mixed $value
   * @return bool
   * @phpstan-assert-if-true Entry $value
   * @noinspection PhpUndefinedFieldInspection
   */
  public function is(mixed $value): bool {
    try {
      return (
        $value instanceof Entry &&
        $value->getType()->handle == $this->value
      );
    } catch (Throwable) {
      return false;
    }
  }


  // Static methods
  // --------------

  /**
   * @param mixed $value
   * @param EntryTypeTrait[] $entryTypes
   * @return bool
   * @phpstan-assert-if-true Entry $value
   */
  static public function isAny(mixed $value, array $entryTypes): bool {
    foreach ($entryTypes as $entryType) {
      if ($entryType->is($value)) {
        return true;
      }
    }

    return false;
  }
}
