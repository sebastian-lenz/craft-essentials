<?php

namespace lenz\craft\essentials\twig\queries\filters;

use craft\web\Request;

/**
 * Class AbstractValueFilter
 */
abstract class AbstractValueFilter extends AbstractFilter
{
  /**
   * @return string
   */
  abstract function getName() : string;

  /**
   * @param string $value
   */
  abstract function setValue(string $value);

  /**
   * @inheritDoc
   */
  public function getParameters() : array {
    $value = $this->getValue();
    if (empty($value)) {
      return [];
    }

    return [
      $this->getName() => $value
    ];
  }

  /**
   * @return string|null
   */
  public function getValue() : ?string {
    return null;
  }

  /**
   * @param Request $request
   */
  public function setRequest(Request $request) {
    if ($this->allowCustomFilter()) {
      $custom = $request->getParam($this->getName());

      if (!is_null($custom)) {
        $this->setValue($custom);
      }
    }
  }
}
