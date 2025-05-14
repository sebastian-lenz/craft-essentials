<?php

namespace lenz\craft\essentials\services\redirectNotFound\formats;

use Craft;
use craft\elements\Asset;
use craft\models\Volume;
use lenz\craft\essentials\services\redirectNotFound\utils\ElementRef;

/**
 * Class AssetUrlFormat
 */
class AssetUrlFormat extends UrlFormat
{
  /**
   * @var string
   */
  const PREFIX = '#asset:';


  /**
   * @inerhitDoc
   */
  public function canDecode(string $url): bool {
    return str_starts_with($url, self::PREFIX);
  }

  /**
   * @inerhitDoc
   */
  public function decode(string $url): ?string {
    $ref = ElementRef::parse($url);

    $asset = Asset::findOne(['id' => $ref->id]);
    $url = $asset?->url;
    if ($ref->hash) {
      $url .= '#' . $ref->hash;
    }

    return $url;
  }

  /**
   * @inerhitDoc
   */
  public function encode(string $url): ?string {
    $asset = $this->findAsset($url);
    if (!$asset) {
      return null;
    }

    return (string)ElementRef::fromElement($asset, $url);
  }


  // Private methods
  // ---------------

  /**
   * @param string $url
   * @return Asset|null
   */
  private function findAsset(string $url): Asset|null {
    $volume = $this->findVolume($url);
    if (!$volume) {
      return null;
    }

    $assets = Craft::$app->getAssets();
    $url = trim(substr($url, strlen($volume->getRootUrl())), '/');
    $splitAt = strrpos($url, '/');

    if ($splitAt !== false) {
      $fileName = trim(substr($url, $splitAt), '/');
      $folder = $assets->findFolder([
        'path' => trim(substr($url, 0, $splitAt), '/'),
        'volumeId' => $volume->id,
      ]);
    } else {
      $fileName = $url;
      $folder = $assets->getRootFolderByVolumeId($volume->id);
    }

    if (!$folder) {
      return null;
    }

    return Asset::findOne([
      'filename' => $fileName,
      'folder' => $folder,
      'volume' => $volume,
    ]);
  }


  /**
   * @param string $url
   * @return Volume|null
   */
  private function findVolume(string $url): Volume|null {
    $volumes = Craft::$app->getVolumes()->getAllVolumes();
    foreach ($volumes as $volume) {
      $rootUrl = $volume->getRootUrl();
      if (empty($rootUrl)) {
        continue;
      }

      if (str_starts_with($url, $rootUrl)) {
        return $volume;
      }
    }

    return null;
  }
}
