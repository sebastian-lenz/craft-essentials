<?php

namespace lenz\craft\essentials\console\controllers;

use craft\elements\Asset;
use lenz\craft\essentials\Plugin;
use lenz\craft\essentials\services\imagePlaceholder\GeneratePlaceholderJob;
use lenz\craft\essentials\services\imagePlaceholder\ImagePlaceholder;
use yii\console\Controller;

/**
 * Class PlaceholderController
 */
class PlaceholderController extends Controller
{
  /**
   * Compile all gettext files.
   */
  public function actionIndex(): void {
    $volumes = Plugin::getInstance()->getSettings()->imagePlaceholderVolumes;
    $query = Asset::find();
    if (!empty($volumes)) {
      $query->volume($volumes);
    }

    $assetIds = $query->select(['id'])->column();

    foreach ($assetIds as $assetId) {
      \Craft::$app->getQueue()->push(
        new GeneratePlaceholderJob([
          'assetId' => $assetId,
          'fieldHandle' => ImagePlaceholder::$FIELD_HANDLE,
          'size' => ImagePlaceholder::$SIZE,
        ])
      );
    }
  }
}
