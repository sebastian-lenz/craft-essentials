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
use craft\volumes\Local;
use lenz\craft\essentials\Plugin;
use lenz\craft\essentials\services\imageCompressor\jobs\TransformIndexJob;
use lenz\craft\essentials\services\siteMap\SiteMapService;
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
  static private $_INSTANCE;


  /**
   * @inheritDoc
   */
  public function init() {
    if (!Plugin::getInstance()->getSettings()->enableWebp) {
      return;
    }

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
   * @param AssetTransformIndex $index
   * @return string|null
   * @throws InvalidConfigException
   */
  public function getWebpPath(Asset $asset, AssetTransformIndex $index): ?string {
    $fileName = TransformIndexJob::resolveTransformFileName($index, $asset);
    if (is_null($fileName)) {
      return null;
    }

    return $fileName . '.webp';
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
   * @throws ImageException
   */
  public function onGenerateTransform(GenerateTransformEvent $event) {
    $asset = $event->asset;
    $index = $event->transformIndex;
    $transformPath = $this->getWebpPath($asset, $index);
    if (is_null($transformPath)) {
      return;
    }

    $dir = dirname($transformPath);
    if (!file_exists($dir)) {
      mkdir($dir, 0777, true);
    }

    $event->image->saveAs($transformPath);
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
