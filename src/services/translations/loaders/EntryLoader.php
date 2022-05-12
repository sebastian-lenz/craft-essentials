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
   * @inheritDoc
   */
  public function load(ElementInterface $element): array|false {
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
