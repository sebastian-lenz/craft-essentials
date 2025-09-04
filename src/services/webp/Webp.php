<?php

namespace lenz\craft\essentials\services\webp;

use Craft;
use craft\elements\Asset;
use craft\errors\FsException;
use craft\events\AssetEvent;
use craft\events\ImageTransformerOperationEvent;
use craft\helpers\FileHelper;
use craft\image\Raster;
use craft\imagetransforms\ImageTransformer as NativeImageTransformer;
use craft\models\ImageTransformIndex;
use lenz\craft\essentials\Plugin;
use lenz\craft\essentials\services\eventBus\On;
use lenz\craft\essentials\services\imageCompressor\jobs\TransformIndexJob;
use lenz\craft\utils\helpers\ImageTransforms;
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
   * @var string
   */
  const EVENT_CREATE_DERIVATE = 'createDerivate';


  /**
   * @param Asset $asset
   * @param ImageTransformIndex $index
   * @return string|null
   */
  public function getWebpPath(Asset $asset, ImageTransformIndex $index): ?string {
    $fileName = ImageTransforms::getTransformPath($asset, $index);
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
  #[On(NativeImageTransformer::class, NativeImageTransformer::EVENT_DELETE_TRANSFORMED_IMAGE, [self::class, 'requiresHandler'])]
  public function onAfterDeleteTransforms(ImageTransformerOperationEvent $event): void {
    $asset = $event->asset;
    $fileName = $this->getWebpPath($asset, $event->imageTransformIndex);
    $fs = $asset->getVolume()->getTransformFs();

    if ($fs->fileExists($fileName)) {
      $fs->deleteFile($fileName);
    }
  }

  /**
   * @param ImageTransformerOperationEvent $event
   * @throws InvalidConfigException
   */
  #[On(NativeImageTransformer::class, NativeImageTransformer::EVENT_TRANSFORM_IMAGE, [self::class, 'requiresHandler'])]
  public function onGenerateTransform(ImageTransformerOperationEvent $event): void {
    $image = $event->image;
    if (!$image instanceof Raster) {
      return;
    }

    if (Event::hasHandlers(__CLASS__, self::EVENT_CREATE_DERIVATE)) {
      $createEvent = new AssetEvent([ 'asset' => $event->asset ]);
      Event::trigger(__CLASS__, self::EVENT_CREATE_DERIVATE, $createEvent);

      if ($createEvent->handled || !$createEvent->isValid) {
        return;
      }
    }

    $asset = $event->asset;
    $index = $event->imageTransformIndex;
    $fileName = $this->getWebpPath($asset, $index);
    if (is_null($fileName)) {
      return;
    }

    $fileSystem = $asset->getVolume()->getTransformFs();
    $dirName = dirname($fileName);
    $fileSystem->createDirectory($dirName);

    $imagine = $image->getImagineImage();
    if (is_null($imagine)) {
      return;
    }

    $transform = $index->transform;
    $quality = $transform->quality ?: Craft::$app->getConfig()->getGeneral()->defaultImageQuality;

    $tempFilename = uniqid(pathinfo($index->filename, PATHINFO_FILENAME), true) . '.webp';
    $tempPath = Craft::$app->getPath()->getTempPath() . DIRECTORY_SEPARATOR . $tempFilename;
    $imagine->save($tempPath, [
      'format' => 'webp',
      'optimize' => true,
      'webp_quality' => round($quality * self::$QUALITY_FACTOR),
    ]);

    $stream = fopen($tempPath, 'rb');
    $fileSystem->writeFileFromStream($fileName, $stream, []);
    if (file_exists($tempPath)) {
      FileHelper::unlink($tempPath);
    }
  }


  // Static methods
  // --------------

  /**
   * @return Webp
   */
  public static function getInstance(): Webp {
    return Plugin::getInstance()->webp;
  }

  /**
   * @return bool
   */
  static public function requiresHandler(): bool {
    return Plugin::getInstance()->getSettings()->enableWebp;
  }
}
