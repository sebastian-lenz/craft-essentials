<?php

namespace lenz\craft\essentials\services\gettext\utils;

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
  public function clone() {
    $result = new Translations();
    $result->addFromPoString($this->toPoString());
    return $result;
  }

  /**
   * @param string $original
   */
  public function insertCp($original) {
    if (!empty($original)) {
      $this->insert(self::CP_CONTEXT, $original);
    }
  }
}
