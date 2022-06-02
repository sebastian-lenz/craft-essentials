<?php

namespace lenz\craft\essentials\helpers;

use craft\base\ElementInterface;
use craft\elements\db\ElementQuery;
use craft\errors\InvalidFieldException;

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
    $result = $element->getEagerLoadedElements($attribute);
    if (!is_null($result)) {
      return $result->all();
    }

    try {
      $result = $element->getFieldValue($attribute);
    } catch (InvalidFieldException) {
      return [];
    }

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
