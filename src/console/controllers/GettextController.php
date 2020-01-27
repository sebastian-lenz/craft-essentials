<?php

namespace lenz\craft\essentials\console\controllers;

use yii\console\Controller;
use lenz\craft\essentials\Plugin;

/**
 * Class TranslationsController
 */
class GettextController extends Controller
{
  /**
   * Compile all gettext files.
   */
  public function actionCompile() {
    Plugin::getInstance()->gettext->compile();
  }

  /**
   * Extract all translatable strings.
   *
   * @param string $ignore
   */
  public function actionExtract($ignore = '') {
    Plugin::getInstance()->gettext->extract(explode(',', $ignore));
  }
}
