<?php

namespace lenz\craft\essentials\structs\enum;

use Craft;
use craft\elements\Entry;
use craft\models\Section;
use Throwable;

/**
 * Trait SectionTrait
 *
 * @property string $value
 * @noinspection PhpUnused
 */
trait SectionTrait
{
  /**
   * @return int
   */
  public function getId(): int {
    return $this->getModel()->getId();
  }

  /**
   * @return Section
   */
  public function getModel(): Section {
    return Craft::$app->getEntries()->getSectionByHandle($this->value);
  }

  /**
   * @param mixed $value
   * @return bool
   * @phpstan-assert-if-true Entry $value
   */
  public function is(mixed $value): bool {
    try {
      return (
        $value instanceof Entry &&
        $value->getSection()->handle == $this->value
      );
    } catch (Throwable) {
      return false;
    }
  }


  // Static methods
  // --------------

  /**
   * @param mixed $value
   * @param SectionTrait[] $sections
   * @return bool
   * @phpstan-assert-if-true Entry $value
   */
  static public function isAny(mixed $value, array $sections): bool {
    foreach ($sections as $section) {
      if ($section->is($value)) {
        return true;
      }
    }

    return false;
  }
}
