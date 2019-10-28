<?php

namespace lenz\craft\essentials;

use Craft;
use lenz\craft\utils\elementCache\ElementCache;

/**
 * Class Plugin
 *
 * @property ElementCache $elementCache
 * @property services\FrontendCache $frontendCache
 * @property services\gettext\Gettext $gettext
 * @property services\MailEncoder $mailEncoder
 * @property services\redirectLanguage\RedirectLanguage $redirectLanguage
 * @property services\redirectNotFound\RedirectNotFound $redirectNotFound
 * @property services\RemoveDashboard $removeDashboard
 * @property services\translations\Translations $translations
 * @method Settings getSettings()
 */
class Plugin extends \craft\base\Plugin
{
  /**
   * @var bool
   */
  public $hasCpSettings = true;


  /**
   * @return void
   */
  public function init() {
    parent::init();

    $this->setComponents([
      'elementCache'     => ElementCache::getInstance(),
      'frontendCache'    => services\FrontendCache::getInstance(),
      'gettext'          => services\gettext\Gettext::class,
      'mailEncoder'      => services\MailEncoder::getInstance(),
      'redirectLanguage' => services\redirectLanguage\RedirectLanguage::getInstance(),
      'redirectNotFound' => services\redirectNotFound\RedirectNotFound::getInstance(),
      'removeDashboard'  => services\RemoveDashboard::getInstance(),
      'translations'     => services\translations\Translations::class,
    ]);

    Craft::$app->view->registerTwigExtension(new twig\Extension());
  }

  /**
   * @inheritdoc
   */
  public function createSettingsModel() {
    return new Settings();
  }



  // Protected methods
  // -----------------

  /**
   * @inheritdoc
   */
  protected function settingsHtml() {
    return Craft::$app->view->renderTemplate(
      'lenz-craft-essentials/_settings',
      [
        'settings' => $this->getSettings(),
      ]
    );
  }
}
