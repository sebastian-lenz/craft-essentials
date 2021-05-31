<?php

namespace lenz\craft\essentials\services\imageSharpener;

use craft\events\GenerateTransformEvent;
use craft\image\Raster;
use craft\services\AssetTransforms;
use Imagine\Imagick\Image;
use lenz\craft\essentials\Plugin;
use yii\base\Component;
use yii\base\Event;

/**
 * Class ImageSharpener
 */
class ImageSharpener extends Component
{
  /**
   * @var ImageSharpener
   */
  static private $_instance;


  /**
   * @inheritDoc
   */
  public function init() {
    if (!Plugin::getInstance()->getSettings()->enableImageSharpening) {
      return;
    }

    Event::on(
      AssetTransforms::class,
      AssetTransforms::EVENT_GENERATE_TRANSFORM,
      function(GenerateTransformEvent $event) {
        if ($event->image instanceof Raster) {
          $image = $event->image->getImagineImage();
          if ($image instanceof Image) {
            $image->getImagick()->sharpenImage(1, 0.5);
          } else {
            $image->effects()->sharpen();
          }
        }
      }
    );
  }


  // Static methods
  // --------------

  /**
   * @return ImageSharpener
   */
  public static function getInstance(): ImageSharpener {
    if (!isset(self::$_instance)) {
      self::$_instance = new ImageSharpener();
    }

    return self::$_instance;
  }
}
