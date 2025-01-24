<?php

namespace lenz\craft\essentials\services\imagePlaceholder;

use Craft;
use craft\elements\Asset;
use craft\helpers\App;
use craft\helpers\Image;
use craft\helpers\ImageTransforms;
use craft\image\Raster;
use craft\queue\JobInterface;
use Imagine\Image\AbstractImage;
use lenz\craft\essentials\services\imageCompressor\compressors\AbstractCompressor;
use Throwable;
use yii\base\BaseObject;

/**
 * Class GeneratePlaceholderJob
 */
class GeneratePlaceholderJob extends BaseObject implements JobInterface
{
  /**
   * @var int
   */
  public int $assetId;

  /**
   * @var string
   */
  public string $fieldHandle = 'placeholder';

  /**
   * @var int
   */
  public int $size = 8;

  /**
   * @var array
   */
  CONST FORMATS = ['webp', 'gif', 'png'];


  /**
   * @inheritDoc
   */
  public function getDescription(): ?string {
    return 'Generate placeholder';
  }

  /**
   * @inheritDoc
   * @throws Throwable
   */
  public function execute($queue): void {
    $asset = Asset::findOne($this->assetId);
    if (!$asset || !self::canCreatePlaceholder($asset)) {
      return;
    }

    $placeholder = $this->getImageData($this->getThumbnail($asset));
    $size = strlen($placeholder);
    if ($size > 512) {
      echo "Skipping $asset->id because placeholder is too large ($size).\n";
      echo $placeholder . "\n";
      return;
    }

    $asset->setFieldValue($this->fieldHandle, $placeholder);
    Craft::$app->getElements()->saveElement($asset);
  }


  // Private methods
  // ---------------

  /**
   * @param string $fileName
   * @param string $format
   * @return void
   */
  private function compress(string $fileName, string $format): void {
    static $compressors;
    if (!isset($compressors)) {
      $compressors = AbstractCompressor::createCompressors();
    }

    foreach ($compressors as $compressor) {
      if (
        $compressor->canCompress(mb_strtolower($format)) &&
        $compressor->compress($fileName)
      ) {
        break;
      }
    }
  }

  /**
   * @param AbstractImage $imagine
   * @param string $format
   * @return string|null
   */
  private function saveAsDataUrl(AbstractImage $imagine, string $format): ?string {
    $fileName = App::parseEnv('@runtime/temp/' . uniqid('placeholder', true) . '.' . $format);
    try {
      $imagine->save($fileName, ['animated' => false, 'flatten' => true]);
      $this->compress($fileName, $format);
      $data = file_get_contents($fileName);
    } catch (Throwable) {
      $data = null;
    } finally {
      unlink($fileName);
    }

    return $data ?
      'image/' . $format. ';base64,' . base64_encode($data)
      : null;
  }

  /**
   * @param AbstractImage|null $imagine
   * @return string|null
   */
  private function getImageData(?AbstractImage $imagine): ?string {
    if (is_null($imagine)) {
      return null;
    }

    $result = null;
    foreach (self::FORMATS as $format) {
      $encoded = $this->saveAsDataUrl($imagine, $format);

      if (is_null($encoded)) {
        continue;
      } elseif (is_null($result) || strlen($encoded) < strlen($result)) {
        $result = $encoded;
      }
    }

    return $result;
  }

  /**
   * @param Asset $asset
   * @return AbstractImage|null
   */
  private function getThumbnail(Asset $asset): ?AbstractImage {
    $imagesService = Craft::$app->getImages();
    try {
      $imageSource = ImageTransforms::getLocalImageSource($asset);
    } catch (Throwable) {
      return null;
    }

    $image = $imagesService->loadImage($imageSource);
    if (!($image instanceof Raster)) {
      return null;
    }

    $width = $image->getWidth();
    $height = $image->getHeight();
    $factor = max($width / $this->size, $height / $this->size);
    $image = $image->resize(
      max(1, min($this->size, round($width / $factor))),
      max(1, min($this->size, round($height / $factor)))
    );

    return $image->getImagineImage();
  }


  // Static methods
  // --------------

  /**
   * @param Asset $asset
   * @return bool
   */
  public static function canCreatePlaceholder(Asset $asset): bool {
    try {
      return
        $asset->getMimeType() !== 'image/svg+xml' &&
        Image::canManipulateAsImage(pathinfo($asset->getFilename(), PATHINFO_EXTENSION));
    } catch (Throwable) {
      return false;
    }
  }
}
