<?php

namespace lenz\craft\essentials\services\gettext\sources;

use lenz\craft\essentials\services\gettext\Translations;

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
