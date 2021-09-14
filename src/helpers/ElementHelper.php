<?php

namespace lenz\craft\essentials\helpers;

use craft\base\ElementInterface;
use craft\elements\db\ElementQuery;

/**
 * Class ElementHelper
 */
class ElementHelper extends \craft\helpers\ElementHelper
{
  /**
   * @param ElementInterface $element
   * @param string $attribute
   * @return ElementInterface[]
   */
  static public function eagerLoad(ElementInterface $element, string $attribute): array {
    $result = $element->$attribute;
    if (!is_array($result)) {
      if ($result instanceof ElementQuery) {
        $result = $result->all();
        $element->setEagerLoadedElements($attribute, $result);
      } else {
        $result = [];
      }
    }

    return $result;
  }
}
