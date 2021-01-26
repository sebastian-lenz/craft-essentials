<?php

namespace lenz\craft\essentials\helpers;

use craft\base\ElementInterface;
use craft\elements\db\ElementQuery;
use craft\elements\Entry;

/**
 * Class EntryHelper
 */
class EntryHelper
{
  /**
   * @param Entry $entry
   * @param string $attribute
   * @return ElementInterface[]
   */
  static public function eagerLoad(Entry $entry, string $attribute): array {
    $result = $entry->$attribute;
    if ($result instanceof ElementQuery) {
      $result = $result->all();
      $entry->setEagerLoadedElements($attribute, $result);
    }

    return $result;
  }
}
