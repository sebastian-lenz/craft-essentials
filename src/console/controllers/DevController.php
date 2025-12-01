<?php

namespace lenz\craft\essentials\console\controllers;

use lenz\craft\essentials\services\dev\ProjectEnums;
use yii\console\Controller;

/**
 * Class EnumsController
 */
class DevController extends Controller
{
  /**
   * @return void
   */
  public function actionIndex(): void {
    $projectEnums = new ProjectEnums();
    foreach ($projectEnums->getWriters() as $writer) {
      $writer->write();
    }
  }
}
