<?php

namespace lenz\craft\essentials\console\controllers;

use Craft;
use craft\elements\Asset;
use lenz\craft\essentials\services\imageCompressor\jobs\AssetJob;
use lenz\craft\essentials\services\imageCompressor\jobs\TransformIndexJob;
use yii\console\Controller;

/**
 * Class CompressController
 */
class CompressController extends Controller
{
  /**
   * Compresses all assets.
   */
  public function actionAll() {
    $queue = Craft::$app->queue;
    $transforms = Craft::$app->getAssetTransforms();

    foreach (Asset::find()->all() as $asset) {
      if ($asset->kind != Asset::KIND_IMAGE) {
        continue;
      }

      $queue->push(new AssetJob(['assetId' => $asset->id]));

      foreach ($transforms->getAllCreatedTransformsForAsset($asset) as $index) {
        $queue->push(new TransformIndexJob(['transformIndexId' => $index->id]));
      }
    }
  }
}
