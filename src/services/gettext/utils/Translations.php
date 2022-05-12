<?php

namespace lenz\craft\essentials\services\gettext\utils;

use Gettext\Translation;
use Gettext\Translations as BaseTranslations;

/**
 * Class Translations
 */
class Translations extends BaseTranslations
{
  /**
   * The context used to store cp texts.
   */
  const CP_CONTEXT = 'cp';


  /**
   * @return Translations
   */
  public function clone(): Translations {
    $result = new Translations();
    $result->addFromPoString($this->toPoString());
    return $result;
  }

  /**
   * @param string $original
   * @return Translation|null
   */
  public function insertCp(string $original): ?Translation {
    if (empty($original)) {
      return null;
    }

    return $this->insert(self::CP_CONTEXT, $original);
  }
}
