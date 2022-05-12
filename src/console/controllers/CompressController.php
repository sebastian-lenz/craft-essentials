<?php

namespace lenz\craft\essentials\console\controllers;

use Craft;
use craft\db\Query;
use craft\db\Table;
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

    foreach (Asset::find()->all() as $asset) {
      if ($asset->kind != Asset::KIND_IMAGE) {
        continue;
      }

      $queue->push(new AssetJob(['assetId' => $asset->id]));

      $transformIds = (new Query())
        ->select(['id'])
        ->from([Table::IMAGETRANSFORMINDEX])
        ->where(['assetId' => $asset->id])
        ->column();

      foreach ($transformIds as $transformId) {
        $queue->push(new TransformIndexJob(['transformIndexId' => $transformId]));
      }
    }
  }
}
