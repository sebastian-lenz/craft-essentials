<?php

namespace lenz\craft\essentials\assets;

use craft\web\AssetBundle;

/**
 * Class SitemapAsset
 */
class SitemapAsset extends AssetBundle
{
  /**
   * @return void
   */
  public function init(): void {
    $this->sourcePath = __DIR__ . '/sitemap';
    parent::init();
  }
}
