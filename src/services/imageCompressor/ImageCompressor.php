<?php

namespace lenz\craft\essentials\services\imageCompressor;

use Craft;
use craft\base\Element;
use craft\elements\Asset;
use craft\events\GenerateTransformEvent;
use craft\events\ModelEvent;
use craft\services\AssetTransforms;
use lenz\craft\essentials\Plugin;
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
  static private $_instance;


  /**
   * @inheritDoc
   */
  public function init() {
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
      AssetTransforms::class,
      AssetTransforms::EVENT_GENERATE_TRANSFORM,
      function(GenerateTransformEvent $event) {
        if ($event->asset->kind == Asset::KIND_IMAGE) {
          Craft::$app->getQueue()->push(new jobs\TransformIndexJob([
            'transformIndexId' => $event->transformIndex->id,
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
