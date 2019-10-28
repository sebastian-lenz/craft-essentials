<?php

namespace lenz\craft\essentials\services\translations\loaders;

use craft\base\ElementInterface;
use craft\elements\Entry;

/**
 * Class EntryLoader
 */
class EntryLoader extends AbstractLoader
{
  /**
   * @param ElementInterface $element
   * @return false|ElementInterface[]
   */
  public function load(ElementInterface $element) {
    if (!($element instanceof Entry)) {
      return false;
    }

    return Entry::findAll([
      'id'            => $element->id,
      'site'          => '*',
      'withStructure' => false,
    ]);
  }
}
