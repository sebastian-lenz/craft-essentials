<?php

namespace lenz\craft\essentials\services\redirectNotFound\formats;

use Craft;
use craft\elements\Entry;
use lenz\craft\essentials\services\redirectNotFound\utils\ElementRef;
use lenz\craft\essentials\services\redirectNotFound\utils\SiteUrlHelper;
use Throwable;

/**
 * Class EntryUrlFormat
 */
class EntryUrlFormat extends UrlFormat
{
  /**
   * @var string
   */
  const PREFIX = '#entry:';


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
    $entry = Entry::findOne([
      'id' => $ref->id,
      'siteId' => $ref->siteId,
    ]);

    $url = $entry?->url;
    if ($ref->hash) {
      $url .= '#' . $ref->hash;
    }

    return $url;
  }

  /**
   * @inerhitDoc
   */
  public function encode(string $url): ?string {
    try {
      $site = SiteUrlHelper::getSiteByUri($url);
    } catch (Throwable) {
      return null;
    }

    $path = SiteUrlHelper::trimSitePath($site, $url);
    $element = Craft::$app->getElements()->getElementByUri($path, $site->id, true);
    if (!$element) {
      return null;
    }

    return (string)ElementRef::fromElement($element, $url);
  }
}
