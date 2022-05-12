<?php

namespace lenz\craft\essentials\services\imageCompressor;

use Craft;
use craft\elements\Asset;
use craft\models\ImageTransformIndex;
use yii\base\InvalidConfigException;

/**
 * Class ImageTransformer
 */
class ImageTransformer extends \craft\imagetransforms\ImageTransformer
{
  /**
   * @param Asset $asset
   * @param ImageTransformIndex $transformIndex
   * @return string
   * @throws InvalidConfigException
   */
  public function getTransformPath(Asset $asset, ImageTransformIndex $transformIndex): string {
    return $this->getTransformBasePath($asset) . $this->getTransformSubpath($asset, $transformIndex);
  }


  // Static methods
  // --------------

  /**
   * @return ImageTransformer
   * @throws InvalidConfigException
   */
  static public function getInstance(): ImageTransformer {
    return Craft::$app->imageTransforms->getImageTransformer(self::class);
  }
}
