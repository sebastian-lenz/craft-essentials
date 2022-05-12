<?php

namespace lenz\craft\essentials\twig\queries\filters;

use craft\web\Request;
use lenz\craft\utils\models\UrlParameter;

/**
 * Class AbstractValueFilter
 */
abstract class AbstractValueFilter extends AbstractFilter
{
  /**
   * @return string
   */
  abstract function getName(): string;

  /**
   * @param string $value
   */
  abstract function setValue(string $value);

  /**
   * @inheritDoc
   */
  public function getParameters(): array {
    $value = $this->getValue();
    if (empty($value)) {
      return [];
    }

    return [
      $this->getName() => $value
    ];
  }

  /**
   * @return string|UrlParameter|null
   */
  public function getValue(): UrlParameter|string|null {
    return null;
  }

  /**
   * @param Request $request
   */
  public function setRequest(Request $request): void {
    if ($this->allowCustomFilter()) {
      $custom = $request->getParam($this->getName());

      if (!is_null($custom)) {
        $this->setValue($custom);
      }
    }
  }
}
