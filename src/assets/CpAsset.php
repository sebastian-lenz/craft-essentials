<?php

namespace lenz\craft\essentials\assets;

use craft\web\AssetBundle;

/**
 * Class CpAsset
 */
class CpAsset extends AssetBundle
{
  /**
   * @return void
   */
  public function init(): void {
    $this->sourcePath = __DIR__ . '/cp';
    $this->css = [ 'styles.css' ];

    parent::init();
  }


  // Static methods
  // --------------

  /**
   * @return void
   */
  static public function autoRegister(): void {
    static $isRegistered = false;

    if (!$isRegistered) {
      self::register(\Craft::$app->getView());
      $isRegistered = true;
    }
  }
}
