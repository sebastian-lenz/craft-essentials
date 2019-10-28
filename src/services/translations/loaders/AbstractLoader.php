<?php

namespace lenz\craft\essentials\services\translations\loaders;

use craft\base\ElementInterface;

/**
 * Class AbstractLoader
 */
abstract class AbstractLoader
{
  /**
   * @param ElementInterface $element
   * @return ElementInterface[]
   */
  abstract public function load(ElementInterface $element);


  // Static methods
  // --------------

  /**
   * @return AbstractLoader[]
   */
  static public function getLoaders() {
    return [
      new EntryLoader(),
      new EventLoader(),
    ];
  }
}
