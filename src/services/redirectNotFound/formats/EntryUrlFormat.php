<?php

namespace lenz\craft\essentials\services\redirectNotFound\formats;

use Craft;
use craft\elements\Entry;
use craft\models\Site;
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
    $url = substr($url, strlen(self::PREFIX));
    list($id, $rest) = array_pad(explode('@', $url, 2), 2, null);
    list($siteId, $hash) = array_pad(explode('#', $rest, 2), 2, null);

    $entry = Entry::findOne([
      'id' => $id,
      'siteId' => $siteId,
    ]);

    $url = $entry?->url;
    if ($hash) {
      $url .= '#' . $hash;
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

    $hashPosition = strpos($url, '#');
    $hash = $hashPosition ? substr($url, $hashPosition) : '';

    return '#entry:' . $element->id . '@' . $site->id . $hash;
  }
}
