<?php

namespace lenz\craft\essentials\services\cp\linktypes;

use craft\helpers\ArrayHelper;

/**
 * Class Url
 */
class Url extends \craft\fields\linktypes\Url
{
  /**
   * @inheritDoc
   */
  public function linkLabel(string $value): string {
    return $value;
  }

  /**
   * @inerhitDoc
   */
  public function normalizeValue(string $value): string {
    if ($this->supports($value)) {
      return $value;
    }

    // Only add a prefix if the end result validates
    $prefix = ArrayHelper::firstValue($this->originalUrlPrefix());
    $normalized = "$prefix$value";
    return $this->validateValue($normalized) ? $normalized : $value;
  }

  /**
   * @inerhitDoc
   */
  public function supports(string $value): bool {
    $value = mb_strtolower($value);
    foreach ($this->originalUrlPrefix() as $prefix) {
      if (str_starts_with($value, $prefix)) {
        return true;
      }
    }

    return false;
  }

  // Protected methods
  // -----------------

  /**
   * @return string[]
   */
  protected function originalUrlPrefix(): array {
    return parent::urlPrefix();
  }

  /**
   * @inerhitDoc
   */
  protected function pattern(): string {
    $prefixes = array_map(fn(string $prefix) => preg_quote($prefix, '/'), $this->originalUrlPrefix());
    return sprintf('^(%s)', implode('|', $prefixes));
  }

  /**
   * @inerhitDoc
   */
  protected function urlPrefix(): array {
    return [];
  }
}
