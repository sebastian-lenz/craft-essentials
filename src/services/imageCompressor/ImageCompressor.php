<?php

namespace lenz\craft\essentials\services\imageCompressor;

use Craft;
use craft\base\Element;
use craft\elements\Asset;
use craft\events\ImageTransformerOperationEvent;
use craft\events\ModelEvent;
use craft\imagetransforms\ImageTransformer;
use lenz\craft\essentials\Plugin;
use lenz\craft\essentials\services\eventBus\On;

/**
 * Class ImageCompressor
 */
class ImageCompressor
{
  /**
   * @param ModelEvent $event
   * @return void
   */
  #[On(Asset::class, Element::EVENT_BEFORE_SAVE)]
  static public function onBeforeSave(ModelEvent $event): void {
    if (
      !empty($event->sender->tempFilePath) &&
      $event->sender->kind == Asset::KIND_IMAGE
    ) {
      Craft::$app->getQueue()->push(new jobs\AssetJob([
        'assetId' => $event->sender->id,
      ]));
    }
  }

  /**
   * @param ImageTransformerOperationEvent $event
   * @return void
   */
  #[On(ImageTransformer::class, ImageTransformer::EVENT_TRANSFORM_IMAGE)]
  static public function onTransformImage(ImageTransformerOperationEvent $event): void {
    if ($event->asset->kind == Asset::KIND_IMAGE) {
      Craft::$app->getQueue()->push(new jobs\TransformIndexJob([
        'transformIndexId' => $event->imageTransformIndex->id,
      ]));
    }
  }


  // Static methods
  // --------------

  /**
   * @return bool
   */
  static public function requiresHandler(): bool {
    return Plugin::getInstance()->getSettings()->enableImageCompressor;
  }
}
