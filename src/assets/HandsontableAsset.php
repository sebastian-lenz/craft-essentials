<?php

namespace lenz\craft\essentials\assets;

use craft\web\AssetBundle;

/**
 * Class HandsontableAsset
 */
class HandsontableAsset extends AssetBundle
{
  /**
   * @return void
   */
  public function init(): void {
    $this->sourcePath = __DIR__ . '/handsontable';
    $this->js         = [ 'handsontable.full.min.js' ];
    $this->css        = [ 'handsontable.full.min.css' ];

    parent::init();
  }
}
