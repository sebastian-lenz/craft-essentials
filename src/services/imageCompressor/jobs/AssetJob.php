<?php

namespace lenz\craft\essentials\services\imageCompressor\jobs;

use Craft;
use craft\elements\Asset;
use craft\errors\AssetLogicException;
use Throwable;

/**
 * Class AssetJob
 */
class AssetJob extends AbstractJob
{
  /**
   * @var int|null
   */
  public $assetId = null;

  /**
   * @var Asset|null
   */
  private $_asset;

  /**
   * @var string|null
   */
  private $_format;


  // Protected methods
  // -----------------

  /**
   * @return void
   */
  protected function afterExecution() {
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
    } catch (Throwable $e) {
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
   * @throws AssetLogicException
   */
  protected function getFormat(): ?string {
    if (isset($this->_format)) {
      return $this->_format;
    }

    $asset = $this->getAsset();
    if (is_null($asset) || $asset->kind != Asset::KIND_IMAGE) {
      return $this->_format = null;
    }

    return $this->_format = Craft::$app
      ->assetTransforms
      ->detectAutoTransformFormat($asset);
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
