<?php

namespace lenz\craft\essentials\services\i18n\sources;

use lenz\craft\essentials\services\i18n\Translations;

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
