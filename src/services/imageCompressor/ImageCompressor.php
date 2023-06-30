<?php

namespace lenz\craft\essentials\services\imageCompressor;

use Craft;
use craft\base\Element;
use craft\elements\Asset;
use craft\events\GenerateTransformEvent;
use craft\events\ImageTransformerOperationEvent;
use craft\events\ModelEvent;
use craft\imagetransforms\ImageTransformer;
use lenz\craft\essentials\Plugin;
use lenz\craft\utils\helpers\ImageTransforms;
use yii\base\Component;
use yii\base\Event;

/**
 * Class ImageCompressor
 */
class ImageCompressor extends Component
{
  /**
   * @var ImageCompressor
   */
  static private ImageCompressor $_instance;


  /**
   * @inheritDoc
   */
  public function init(): void {
    if (!Plugin::getInstance()->getSettings()->enableImageCompressor) {
      return;
    }

    Event::on(
      Asset::class,
      Element::EVENT_BEFORE_SAVE,
      function(ModelEvent $event) {
        if (
          !empty($event->sender->tempFilePath) &&
          $event->sender->kind == Asset::KIND_IMAGE
        ) {
          Craft::$app->getQueue()->push(new jobs\AssetJob([
            'assetId' => $event->sender->id,
          ]));
        }
      }
    );

    Event::on(
      ImageTransformer::class,
      ImageTransformer::EVENT_TRANSFORM_IMAGE,
      function(ImageTransformerOperationEvent $event) {
        if ($event->asset->kind == Asset::KIND_IMAGE) {
          Craft::$app->getQueue()->push(new jobs\TransformIndexJob([
            'transformIndexId' => $event->imageTransformIndex->id,
          ]));
        }
      }
    );
  }


  // Static methods
  // --------------

  /**
   * @return ImageCompressor
   */
  public static function getInstance(): ImageCompressor {
    if (!isset(self::$_instance)) {
      self::$_instance = new ImageCompressor();
    }

    return self::$_instance;
  }
}
