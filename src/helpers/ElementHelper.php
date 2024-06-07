<?php

namespace lenz\craft\essentials\helpers;

use craft\base\ElementInterface;
use craft\elements\db\EagerLoadPlan;
use craft\elements\db\ElementQuery;
use craft\elements\ElementCollection;
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
   * @return ElementCollection
   */
  static public function eagerLoad(ElementInterface $element, string $attribute, callable $modify = null): ElementCollection {
    $result = $element->getEagerLoadedElements($attribute);
    if (!is_null($result)) {
      return $result;
    }

    try {
      $result = match($attribute) {
        'children' => $element->getChildren(),
        default => $element->getFieldValue($attribute),
      };
    } catch (InvalidFieldException) {
      return ElementCollection::make([]);
    }

    if ($result instanceof ElementQuery) {
      $result = ($modify ? $modify($result) : $result)->all();
      $element->setEagerLoadedElements($attribute, $result, new EagerLoadPlan([
        'handle' => $attribute,
        'alias' => $attribute,
      ]));
    } else {
      $result = [];
    }

    return $result instanceof ElementCollection
      ? $result
      : ElementCollection::make($result);
  }
}
