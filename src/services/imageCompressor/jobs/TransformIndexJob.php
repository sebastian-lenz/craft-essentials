<?php

namespace lenz\craft\essentials\services\imageCompressor\jobs;

use Craft;
use craft\elements\Asset;
use craft\errors\AssetLogicException;
use craft\models\AssetTransformIndex;

/**
 * Class AssetJob
 */
class TransformIndexJob extends AbstractJob
{
  /**
   * @var int|null
   */
  public $transformIndexId;

  /**
   * @var Asset|null
   */
  private $_asset;

  /**
   * @var string|null
   */
  private $_format;

  /**
   * @var AssetTransformIndex|null
   */
  private $_transformIndex;


  // Protected methods
  // -----------------

  /**
   * @inheritDoc
   */
  protected function getFileName() {
    $index = $this->getTransformIndex();
    return is_null($index) ? null : $index->filename;
  }

  /**
   * @inheritDoc
   * @throws AssetLogicException
   */
  protected function getFormat() {
    if (isset($this->_format)) {
      return $this->_format;
    }

    $index = $this->getTransformIndex();
    if (is_null($index)) {
      return $this->_format = null;
    }

    if (!empty($index->format)) {
      return $this->_format = $index->format;
    }

    return $this->_format = Craft::$app
      ->assetTransforms
      ->detectAutoTransformFormat($this->getAsset());
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

    $index = $this->getTransformIndex();
    return $this->_asset = is_null($index) || is_null($index->assetId)
      ? null
      : Craft::$app->getAssets()->getAssetById($index->assetId);
  }

  /**
   * @return AssetTransformIndex|null
   */
  private function getTransformIndex() {
    if (isset($this->_transformIndex)) {
      return $this->_transformIndex;
    }

    if (is_null($this->transformIndexId)) {
      return $this->_transformIndex = null;
    }

    return $this->_transformIndex = Craft::$app
      ->assetTransforms
      ->getTransformIndexModelById($this->transformIndexId);
  }
}
