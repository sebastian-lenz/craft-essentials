<?php

namespace lenz\craft\essentials\services\siteMap;

use craft\helpers\Html;
use lenz\craft\essentials\assets\SitemapAsset;

/**
 * Class Writer
 */
class SiteMap
{
  /**
   * @var string[]
   */
  private $_buffer = [];


  /**
   * @param string $loc
   * @param \DateTime|null $lastMod
   * @param string|null $content
   */
  public function addUrl(string $loc, \DateTime $lastMod = null, string $content = null) {
    $chunks = [
      '<url><loc>',
      $this->xmlEncode($loc),
      '</loc>'
    ];

    if (!is_null($lastMod)) {
      $chunks[] = '<lastmod>';
      $chunks[] = $lastMod->format(\DateTime::ATOM);
      $chunks[] = '</lastmod>';
    };

    if (!is_null($content)) {
      $chunks[] = $content;
    };

    $chunks[] = '</url>';
    $this->append(implode('', $chunks));
  }

  /**
   * @return string
   */
  public function getXml(): string {
    $bundle = SitemapAsset::register(\Craft::$app->view);

    return implode("\n", [
      '<?xml version="1.0" encoding="UTF-8"?>',
      '<?xml-stylesheet type="text/xsl" href="', $bundle->baseUrl, '/sitemap.xsl"?>',
      '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">',
        implode("\n", $this->_buffer),
      '</urlset>'
    ]);
  }


  // Private methods
  // ---------------

  /**
   * @param string $value
   */
  private function append(string $value) {
    $this->_buffer[] = $value;
  }


  // Static methods
  // --------------

  /**
   * @param string $value
   * @return string|string[]
   */
  static public function xmlEncode(string $value) {
    return str_replace(
      ['&', "'", '"', '>', '<'],
      ['&amp;', '&apos;', '&quot;', '&gt', '&lt'],
      $value
    );
  }

  /**
   * @param array $attributes
   * @return string
   */
  static public function xmlLink(array $attributes) {
    return '<xhtml:link' . Html::renderTagAttributes($attributes) . '/>';
  }
}
