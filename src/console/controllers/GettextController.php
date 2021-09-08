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
   * @var string[]
   */
  public $excludeFiles = [];

  /**
   * @var string[]
   */
  public $excludeLanguages = [];


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
  public function actionExtract(string $ignore = '') {
    $excludeLanguages = empty($ignore)
      ? $this->excludeLanguages
      : explode(',', $ignore);

    Plugin::getInstance()->gettext
      ->setOptions([
        'excludeFiles' => $this->excludeFiles,
        'excludeLanguages' => $excludeLanguages,
      ])
      ->extract();
  }

  /**
   * @inheritDoc
   */
  public function options($actionID): array {
    if ($actionID == 'extract') {
      return ['excludeFiles', 'excludeLanguages'];
    }

    return [];
  }
}
