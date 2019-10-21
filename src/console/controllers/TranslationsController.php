<?php

namespace lenz\craft\essentials\console\controllers;

use yii\console\Controller;
use lenz\craft\essentials\Plugin;

/**
 * Class TranslationsController
 */
class TranslationsController extends Controller
{
  /**
   * Compile all gettext files.
   */
  public function actionCompile() {
    Plugin::getInstance()->i18n->compile();
  }

  /**
   * Extract all translatable strings.
   */
  public function actionExtract() {
    Plugin::getInstance()->i18n->extract();
  }
}
