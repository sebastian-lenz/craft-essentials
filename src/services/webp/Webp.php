<?php

namespace lenz\craft\essentials\services\webp;

use Craft;
use craft\elements\Asset;
use craft\errors\FsException;
use craft\events\ImageTransformerOperationEvent;
use craft\image\Raster;
use craft\imagetransforms\ImageTransformer as NativeImageTransformer;
use craft\models\ImageTransformIndex;
use lenz\craft\essentials\Plugin;
use lenz\craft\essentials\services\imageCompressor\jobs\TransformIndexJob;
use yii\base\Component;
use yii\base\Event;
use yii\base\InvalidConfigException;

/**
 * Class WebP
 */
class Webp extends Component
{
  /**
   * @var Webp
   */
  static private Webp $_INSTANCE;

  /**
   * @var float
   */
  static float $QUALITY_FACTOR = 1;


  /**
   * @inheritDoc
   */
  public function init() {
    if (!Plugin::getInstance()->getSettings()->enableWebp) {
      return;
    }

    Event::on(
      NativeImageTransformer::class, NativeImageTransformer::EVENT_DELETE_TRANSFORMED_IMAGE,
      [$this, 'onAfterDeleteTransforms']
    );

    Event::on(
      NativeImageTransformer::class, NativeImageTransformer::EVENT_TRANSFORM_IMAGE,
      [$this, 'onGenerateTransform']
    );
  }

  /**
   * @param Asset $asset
   * @param ImageTransformIndex $index
   * @return string|null
   * @throws InvalidConfigException
   */
  public function getWebpPath(Asset $asset, ImageTransformIndex $index): ?string {
    $fileName = TransformIndexJob::resolveTransformFileName($index, $asset);
    if (is_null($fileName)) {
      return null;
    }

    return $fileName . '.webp';
  }

  /**
   * @param ImageTransformerOperationEvent $event
   * @throws InvalidConfigException
   * @throws FsException
   */
  public function onAfterDeleteTransforms(ImageTransformerOperationEvent $event) {
    $asset = $event->asset;
    $fileName = $this->getWebpPath($asset, $event->imageTransformIndex);
    $fs = $asset->getVolume()->getFs();

    if ($fs->fileExists($fileName)) {
      $fs->deleteFile($fileName);
    }
  }

  /**
   * @param ImageTransformerOperationEvent $event
   * @throws InvalidConfigException
   */
  public function onGenerateTransform(ImageTransformerOperationEvent $event) {
    $image = $event->image;
    if (!$image instanceof Raster) {
      return;
    }

    $asset = $event->asset;
    $index = $event->imageTransformIndex;
    $transformPath = $this->getWebpPath($asset, $index);
    if (is_null($transformPath)) {
      return;
    }

    $dir = dirname($transformPath);
    if (!file_exists($dir)) {
      mkdir($dir, 0777, true);
    }

    $imagine = $image->getImagineImage();
    if ($imagine) {
      $transform = $index->transform;
      $quality = $transform->quality ?: Craft::$app->getConfig()->getGeneral()->defaultImageQuality;
      $imagine->save($transformPath, [
        'format' => 'webp',
        'optimize' => true,
        'webp_quality' => round($quality * self::$QUALITY_FACTOR),
      ]);
    }
  }


  // Static methods
  // --------------

  /**
   * @return Webp
   */
  public static function getInstance(): Webp {
    if (!isset(self::$_INSTANCE)) {
      self::$_INSTANCE = new Webp();
    }

    return self::$_INSTANCE;
  }
}
