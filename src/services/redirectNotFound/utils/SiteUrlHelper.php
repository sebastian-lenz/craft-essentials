<?php

namespace lenz\craft\essentials\services\redirectNotFound\utils;

use Craft;
use craft\errors\SiteNotFoundException;
use craft\models\Site;

/**
 * Class SiteUrlHelper
 */
class SiteUrlHelper
{
  /**
   * @param string $url
   * @param bool $useFallback
   * @return Site
   * @throws SiteNotFoundException
   */
  static public function getSiteByUri(string $url, bool $useFallback = false): Site {
    $sites = Craft::$app->getSites();
    $bestScore = 0;
    $bestSite = $useFallback ? $sites->getPrimarySite() : null;

    $urlInfo = parse_url($url);
    if ($urlInfo === false) {
      return $bestSite;
    }

    foreach ($sites->getAllSites() as $site) {
      $score = self::getUrlScore($site, $urlInfo);
      if ($score > $bestScore) {
        $bestScore = $score;
        $bestSite = $site;
      }
    }

    return $bestSite;
  }

  /**
   * @param Site $site
   * @param string $url
   * @return string
   */
  static public function trimSitePath(Site $site, string $url): string {
    $path = parse_url($url, PHP_URL_PATH);
    $path = $path ? self::normalizePath($path) : null;

    $siteUrl = $site->getBaseUrl();
    $siteUrl = $siteUrl ? rtrim($siteUrl, '/') : '';
    $sitePath = $siteUrl ? parse_url($siteUrl, PHP_URL_PATH) : null;
    $sitePath = $sitePath ? self::normalizePath($sitePath) : null;

    if (
      !empty($path) &&
      !empty($sitePath) &&
      str_starts_with($path . '/', $sitePath . '/')
    ) {
      $url = ltrim(substr($path, strlen($sitePath)), '/');
    }

    return $url;
  }


  // Private methods
  // ---------------

  /**
   * @param Site $site
   * @param array $urlInfo
   * @return int
   */
  static private function getUrlScore(Site $site, array $urlInfo): int {
    if (($siteInfo = parse_url($site->baseUrl)) === false) {
      return 0;
    }

    if (
      !empty($urlInfo['host']) &&
      !empty($siteInfo['host']) &&
      $urlInfo['host'] != $siteInfo['host']
    ) {
      return 0;
    }

    $sitePath = empty($siteInfo['path']) ? '' : self::normalizePath($siteInfo['path']);
    $urlPath = empty($urlInfo['path']) ? '' : self::normalizePath($urlInfo['path']) . '/';
    if ($sitePath && !str_starts_with($urlPath, $sitePath . '/')) {
      return 0;
    }

    return 1000 + strlen($sitePath) * 100;
  }

  /**
   * @param string $path
   * @return string
   */
  static private function normalizePath(string $path): string {
    return preg_replace('/\/\/+/', '/', trim($path, '/'));
  }
}
