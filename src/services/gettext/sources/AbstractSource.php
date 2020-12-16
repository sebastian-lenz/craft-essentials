<?php

namespace lenz\craft\essentials\services\gettext\sources;

use lenz\craft\essentials\services\gettext\Gettext;
use lenz\craft\essentials\services\gettext\utils\Translations;

/**
 * Class AbstractSource
 */
abstract class AbstractSource
{
  /**
   * @var Gettext
   */
  protected $_gettext;


  /**
   * AbstractSource constructor.
   *
   * @param Gettext $gettext
   */
  public function __construct(Gettext $gettext) {
    $this->_gettext = $gettext;
  }

  /**
   * @param Translations $translations
   */
  abstract function extract(Translations $translations);
}
