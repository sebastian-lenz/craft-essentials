<?php

namespace lenz\craft\essentials\services\imageCompressor\jobs;

use Craft;
use craft\elements\Asset;
use craft\errors\AssetOperationException;
use craft\helpers\ImageTransforms;
use Throwable;

/**
 * Class AssetJob
 */
class AssetJob extends AbstractJob
{
  /**
   * @var int|null
   */
  public ?int $assetId = null;

  /**
   * @var Asset|null
   */
  private ?Asset $_asset;

  /**
   * @var string|null
   */
  private ?string $_format;


  // Protected methods
  // -----------------

  /**
   * @return void
   */
  protected function afterExecution(): void {
    $asset = $this->getAsset();
    $fileName = $this->getFileName();
    if (is_null($asset) || is_null($fileName)) {
      return;
    }

    $size = filesize($fileName);
    if ($asset->size == $size) {
      return;
    }

    try {
      $asset->size = $size;
      Craft::$app->elements->saveElement($asset);
    } catch (Throwable) {
      // Ignore this
    }
  }

  /**
   * @inheritDoc
   */
  protected function getFileName(): ?string {
    $asset = $this->getAsset();
    return is_null($asset)
      ? null
      : $asset->getImageTransformSourcePath();
  }

  /**
   * @inheritDoc
   * @throws AssetOperationException
   */
  protected function getFormat(): ?string {
    if (isset($this->_format)) {
      return $this->_format;
    }

    $asset = $this->getAsset();
    if (is_null($asset) || $asset->kind != Asset::KIND_IMAGE) {
      return $this->_format = null;
    }

    return $this->_format = ImageTransforms::detectTransformFormat($asset);
  }


  // Private methods
  // ---------------

  /**
   * @return Asset|null
   */
  private function getAsset(): ?Asset {
    if (isset($this->_asset)) {
      return $this->_asset;
    }

    if (is_null($this->assetId)) {
      return $this->_asset = null;
    }

    return $this->_asset = Craft::$app
      ->getAssets()
      ->getAssetById($this->assetId);
  }
}
