<?php

namespace lenz\craft\essentials\services\imageCompressor\jobs;

use Craft;
use craft\base\LocalFsInterface;
use craft\elements\Asset;
use craft\errors\AssetOperationException;
use craft\models\ImageTransformIndex;
use lenz\craft\utils\helpers\ImageTransforms;
use yii\base\InvalidConfigException;

/**
 * Class TransformIndexJob
 */
class TransformIndexJob extends AbstractJob
{
  /**
   * @var int|null
   */
  public ?int $transformIndexId;

  /**
   * @var Asset|null
   */
  private ?Asset $_asset;

  /**
   * @var string|null
   */
  private ?string $_fileName;

  /**
   * @var string|null
   */
  private ?string $_format;

  /**
   * @var ImageTransformIndex|null
   */
  private ?ImageTransformIndex $_transformIndex;


  // Protected methods
  // -----------------

  /**
   * @inheritDoc
   * @throws InvalidConfigException
   */
  protected function getFileName(): ?string {
    if (!isset($this->_fileName)) {
      $index = $this->getTransformIndex();
      $this->_fileName = self::resolveTransformFileName($index);
    }

    return $this->_fileName;
  }

  /**
   * @inheritDoc
   * @throws AssetOperationException
   * @throws InvalidConfigException
   */
  protected function getFormat(): ?string {
    if (isset($this->_format)) {
      return $this->_format;
    }

    $index = $this->getTransformIndex();
    if (is_null($index)) {
      return $this->_format = null;
    }

    if (!empty($index->format)) {
      $this->_format = empty($index->format);
    } else {
      $asset = $this->getAsset();
      $this->_format = $asset ? ImageTransforms::detectTransformFormat($asset) : null;
    }

    return $this->_format;
  }


  // Private methods
  // ---------------

  /**
   * @return Asset|null
   * @throws InvalidConfigException
   */
  private function getAsset(): ?Asset {
    if (isset($this->_asset)) {
      return $this->_asset;
    }

    $index = $this->getTransformIndex();
    return $this->_asset = is_null($index) || is_null($index->assetId)
      ? null
      : Craft::$app->getAssets()->getAssetById($index->assetId);
  }

  /**
   * @return ImageTransformIndex|null
   * @throws InvalidConfigException
   */
  private function getTransformIndex(): ?ImageTransformIndex {
    if (isset($this->_transformIndex)) {
      return $this->_transformIndex;
    }

    if (is_null($this->transformIndexId)) {
      return $this->_transformIndex = null;
    }

    $this->_transformIndex = ImageTransforms::getTransformer()
      ->getTransformIndexModelById($this->transformIndexId);

    return $this->_transformIndex;
  }


  // Static methods
  // --------------

  /**
   * @param ImageTransformIndex|null $index
   * @param Asset|null $asset
   * @return string|null
   * @throws InvalidConfigException
   */
  static public function resolveTransformFileName(?ImageTransformIndex $index, ?Asset $asset = null): ?string {
    if (is_null($index)) {
      return null;
    }

    $asset = $asset ?? Craft::$app->getAssets()->getAssetById($index->assetId);
    $fileSystem = $asset->getVolume()->getFs();
    if (!($fileSystem instanceof LocalFsInterface)) {
      return null;
    }

    return implode(DIRECTORY_SEPARATOR, [
      $fileSystem->getRootPath(),
      ImageTransforms::getTransformPath($asset, $index)
    ]);
  }
}
