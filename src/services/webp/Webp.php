<?php

namespace lenz\craft\essentials\services\webp;

use Craft;
use craft\elements\Asset;
use craft\errors\ImageException;
use craft\errors\VolumeException;
use craft\errors\VolumeObjectExistsException;
use craft\events\AssetTransformImageEvent;
use craft\events\GenerateTransformEvent;
use craft\helpers\FileHelper;
use craft\models\AssetTransformIndex;
use craft\services\AssetTransforms;
use lenz\craft\essentials\services\siteMap\SiteMapService;
use yii\base\Event;
use yii\base\InvalidConfigException;

/**
 * Class WebP
 */
class Webp
{
  /**
   * @var Webp
   */
  static private $_INSTANCE;


  /**
   * Webp constructor.
   */
  public function __construct() {
    Event::on(
      AssetTransforms::class, AssetTransforms::EVENT_AFTER_DELETE_TRANSFORMS,
      [$this, 'onAfterDeleteTransforms']
    );

    Event::on(
      AssetTransforms::class, AssetTransforms::EVENT_GENERATE_TRANSFORM,
      [$this, 'onGenerateTransform']
    );
  }

  /**
   * @param Asset $asset
   * @param AssetTransformIndex $transformIndex
   * @return string
   */
  public function getWebpPath(Asset $asset, AssetTransformIndex $transformIndex): string {
    return implode('', [
      $asset->folderPath,
      Craft::$app->assetTransforms->getTransformSubpath($asset, $transformIndex),
      '.webp'
    ]);
  }

  /**
   * @throws VolumeException
   * @throws InvalidConfigException
   */
  public function onAfterDeleteTransforms(AssetTransformImageEvent $event) {
    $asset = $event->asset;
    $volume = $asset->getVolume();
    $fileName = $this->getWebpPath($asset, $event->transformIndex);

    if ($volume->fileExists($fileName)) {
      $volume->deleteFile($fileName);
    }
  }

  /**
   * @param GenerateTransformEvent $event
   * @throws InvalidConfigException
   * @throws VolumeException
   * @throws ImageException
   */
  public function onGenerateTransform(GenerateTransformEvent $event) {
    $asset = $event->asset;
    $index = $event->transformIndex;
    $transformPath = $this->getWebpPath($asset, $index);

    $tempFilename = uniqid(pathinfo($index->filename, PATHINFO_FILENAME), true) . '.webp';
    $tempPath = Craft::$app->getPath()->getTempPath() . DIRECTORY_SEPARATOR . $tempFilename;
    $event->image->saveAs($tempPath);

    clearstatcache(true, $tempPath);
    $stream = fopen($tempPath, 'rb');

    try {
      $asset->getVolume()->createFileByStream($transformPath, $stream, []);
    } catch (VolumeObjectExistsException $e) {
      // We're fine with that.
    }

    FileHelper::unlink($tempPath);
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
