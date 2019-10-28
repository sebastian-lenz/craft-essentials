<?php

namespace lenz\craft\essentials\services\gettext\sources;

use lenz\craft\essentials\services\gettext\utils\Translations;

/**
 * Class AbstractSource
 */
abstract class AbstractSource
{
  /**
   * @param Translations $translations
   */
  abstract function extract(Translations $translations);
}
