<?php

namespace lenz\craft\essentials\services\imageSharpener;

use craft\events\ImageTransformerOperationEvent;
use craft\image\Raster;
use craft\imagetransforms\ImageTransformer;
use Imagine\Imagick\Image;
use lenz\craft\essentials\Plugin;
use lenz\craft\essentials\services\eventBus\On;

/**
 * Class ImageSharpener
 */
class ImageSharpener
{
  /**
   * @param ImageTransformerOperationEvent $event
   * @return void
   */
  #[On(ImageTransformer::class, ImageTransformer::EVENT_TRANSFORM_IMAGE, [self::class, 'requiresHandler'])]
  static public function onTransformImage(ImageTransformerOperationEvent $event): void {
    if ($event->image instanceof Raster) {
      $image = $event->image->getImagineImage();
      if ($image instanceof Image) {
        $image->getImagick()->sharpenImage(1, 0.5);
      } else {
        $image->effects()->sharpen();
      }
    }
  }

  /**
   * @return bool
   */
  static public function requiresHandler(): bool {
    return Plugin::getInstance()->getSettings()->enableImageSharpening;
  }
}
