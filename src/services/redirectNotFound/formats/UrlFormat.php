<?php

namespace lenz\craft\essentials\services\redirectNotFound\formats;

use Craft;
use craft\errors\SiteNotFoundException;
use yii\base\Event;

/**
 * Class UrlFormat
 */
abstract class UrlFormat
{
  /**
   * @var string
   */
  const CREATE_FORMATS = 'createFormats';


  /**
   * @param string $url
   * @return bool
   */
  abstract function canDecode(string $url): bool;

  /**
   * @param string $url
   * @return string|null
   */
  abstract function decode(string $url): ?string;

  /**
   * @param string $url
   * @return string|null
   */
  abstract function encode(string $url): ?string;


  // Static methods
  // --------------

  /**
   * @param mixed $url
   * @param bool $absolute
   * @return string
   */
  static public function decodeUrl(mixed $url, bool $absolute = false): string {
    if (!is_string($url) || empty($url)) {
      return '';
    }

    $formats = self::getUrlFormats();
    foreach ($formats as $format) {
      $result = $format->canDecode($url) ? $format->decode($url) : null;
      if (!is_null($result)) {
        $url = $result;
        break;
      }
    }

    return $absolute ? self::toAbsoluteUrl($url) : $url;
  }

  /**
   * @param string $url
   * @return string
   */
  static public function encodeUrl(string $url): string {
    foreach (self::getUrlFormats() as $format) {
      $result = $format->encode($url);
      if (!is_null($result)) {
        return $result;
      }
    }

    return $url;
  }

  /**
   * @return UrlFormat[]
   */
  static public function getUrlFormats(): array {
    static $formats;
    if (!isset($formats)) {
      $event = new Event([ 'data' => [new EntryUrlFormat()] ]);
      Event::trigger(UrlFormat::class, UrlFormat::CREATE_FORMATS, $event);
      $formats = $event->data;
    }

    return $formats;
  }

  /**
   * @param string $value
   * @return bool
   */
  static public function isUrlFormat(string $value): bool {
    foreach (self::getUrlFormats() as $urlFormat) {
      if ($urlFormat->canDecode($value)) {
        return true;
      }
    }

    return false;
  }

  /**
   * @param string $url
   * @return string
   */
  static public function toAbsoluteUrl(string $url): string {
    $url = trim($url);
    if (empty($url)) {
      return $url;
    }

    if (str_starts_with($url, '/')) {
      try {
        $site = Craft::$app->getSites()->getCurrentSite();
        $url = $site->baseUrl . $url;
      } catch (SiteNotFoundException) {
        // Ignore
      }
    } elseif (!strpos($url, '://')) {
      $url = 'https://' . $url;
    }

    return $url;
  }
}
