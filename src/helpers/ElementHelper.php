<?php

namespace lenz\craft\essentials\helpers;

use craft\base\ElementInterface;
use craft\elements\db\ElementQuery;
use craft\errors\InvalidFieldException;
use Illuminate\Support\Collection;

/**
 * Class ElementHelper
 */
class ElementHelper extends \craft\helpers\ElementHelper
{
  /**
   * @param ElementInterface $element
   * @param string $attribute
   * @param callable|null $modify
   * @return ElementInterface[]
   */
  static public function eagerLoad(ElementInterface $element, string $attribute, callable $modify = null): array {
    $result = $element->getEagerLoadedElements($attribute);
    if (!is_null($result)) {
      return $result->all();
    }

    try {
      $result = match($attribute) {
        'children' => $element->getChildren(),
        default => $element->getFieldValue($attribute),
      };
    } catch (InvalidFieldException) {
      return [];
    }

    if ($result instanceof Collection) {
      $result = $result->toArray();
    } elseif ($result instanceof ElementQuery) {
      $result = ($modify ? $modify($result) : $result)->all();
      $element->setEagerLoadedElements($attribute, $result);
    } else {
      $result = [];
    }

    return $result;
  }
}
