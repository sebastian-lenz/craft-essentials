<?php

namespace lenz\craft\essentials\services\imagePlaceholder;

use Craft;
use craft\base\Element;
use craft\elements\Asset;
use craft\events\AssetEvent;
use Throwable;
use yii\base\Event;
use yii\base\ModelEvent;

/**
 * Class ImagePlaceholder
 */
class ImagePlaceholder
{
  /**
   * @var string
   */
  static string $FIELD_HANDLE = 'placeholder';

  /**
   * @var int
   */
  static int $SIZE = 8;

  /**
   * @var string|string[]
   */
  static string|array $VOLUMES = '*';


  /**
   * @return void
   */
  static function register(): void {
    Event::on(Asset::class, Asset::EVENT_BEFORE_HANDLE_FILE, self::onBeforeHandleFile(...));
  }


  // Static private methods
  // ----------------------

  /**
   * @param mixed $element
   * @return bool
   */
  static private function isAssetWithPlaceholder(mixed $element): bool {
    if (!($element instanceof Asset)) {
      return false;
    }

    try {
      $volume = $element->getVolume()->handle;
      if (is_array(self::$VOLUMES) && !in_array($volume, self::$VOLUMES)) {
        return false;
      }
    } catch (Throwable) {
      return false;
    }

    return true;
  }

  /**
   * @param ModelEvent $event
   * @return void
   */
  static private function onAfterAssetSave(ModelEvent $event): void {
    Craft::$app->getQueue()->push(
      new GeneratePlaceholderJob(['assetId' => $event->sender->id])
    );
  }

  /**
   * @param AssetEvent $event
   * @return void
   */
  static private function onBeforeHandleFile(AssetEvent $event): void {
    $asset = $event->asset;
    if (!self::isAssetWithPlaceholder($asset)) {
      return;
    }

    if (!empty($asset->id)) {
      Craft::$app->getQueue()->push(
        new GeneratePlaceholderJob([
          'assetId' => $asset->id,
          'fieldHandle' => self::$FIELD_HANDLE,
          'size' => self::$SIZE,
        ])
      );
    } elseif ($event->name == Asset::EVENT_BEFORE_HANDLE_FILE) {
      $asset->on(Element::EVENT_AFTER_SAVE, self::onAfterAssetSave(...));
    }
  }
}
