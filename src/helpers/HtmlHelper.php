<?php

namespace lenz\craft\essentials\helpers;

use craft\helpers\Html;

/**
 * Class HtmlHelper
 */
class HtmlHelper extends Html
{
  /**
   * @return string
   */
  static public function joinClassNames(): string {
    $args = func_get_args();
    $result = [];

    foreach ($args as $arg) {
      if (is_string($arg)) {
        $arg = explode(' ', $arg);
      }

      if (!is_array($arg)) {
        continue;
      }

      foreach ($arg as $key => $value) {
        if (!$value) continue;
        $className = is_numeric($key) ? $value : $key;

        if (!empty($className) && !in_array($className, $result)) {
          $result[] = $className;
        }
      }
    }

    return implode(' ', $result);
  }
}
