<?php

namespace lenz\craft\essentials\services\siteMap;

use Craft;
use craft\helpers\Html;
use DateTime;
use DateTimeInterface;
use lenz\craft\essentials\assets\SitemapAsset;

/**
 * Class Writer
 */
class SiteMap
{
  /**
   * @var string[]
   */
  private array $_buffer = [];


  /**
   * @param string $loc
   * @param DateTime|null $lastMod
   * @param string|null $content
   */
  public function addUrl(string $loc, DateTime $lastMod = null, string $content = null): void {
    $chunks = [
      '<url><loc>',
      $this->xmlEncode($loc),
      '</loc>'
    ];

    if (!is_null($lastMod)) {
      $chunks[] = '<lastmod>';
      $chunks[] = $lastMod->format(DateTimeInterface::ATOM);
      $chunks[] = '</lastmod>';
    }

    if (!is_null($content)) {
      $chunks[] = $content;
    }

    $chunks[] = '</url>';
    $this->append(implode('', $chunks));
  }

  /**
   * @return string
   * @noinspection HttpUrlsUsage
   * @noinspection XmlUnusedNamespaceDeclaration
   */
  public function getXml(): string {
    $bundle = SitemapAsset::register(Craft::$app->view);

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
  private function append(string $value): void {
    $this->_buffer[] = $value;
  }


  // Static methods
  // --------------

  /**
   * @param string $value
   * @return string
   */
  static public function xmlEncode(string $value): string {
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
  static public function xmlLink(array $attributes): string {
    return '<xhtml:link' . Html::renderTagAttributes($attributes) . '/>';
  }
}
