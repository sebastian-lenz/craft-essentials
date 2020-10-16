<?php

namespace lenz\craft\essentials\services\imageCompressor\jobs;

use Craft;
use craft\elements\Asset;

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


  /**
   * @inheritDoc
   */
  public function execute($queue) {
    parent::execute($queue);

    $asset = $this->getAsset();
    $fileName = $this->getFileName();

    if (!is_null($asset) && !is_null($fileName)) {
      $size = filesize($fileName);

      if ($asset->size != $size) {
        $asset->size = $size;
        Craft::$app->elements->saveElement($asset);
      }
    }
  }

  // Protected methods
  // -----------------

  /**
   * @inheritDoc
   */
  protected function getFileName() {
    $asset = $this->getAsset();
    return is_null($asset) ? null : $asset->getImageTransformSourcePath();
  }

  /**
   * @inheritDoc
   */
  protected function getFormat() {
    if (isset($this->_format)) {
      return $this->_format;
    }

    $asset = $this->getAsset();
    if (is_null($asset)) {
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
  private function getAsset() {
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
