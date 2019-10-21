<?php

namespace lenz\craft\essentials;

use Craft;
use lenz\craft\essentials\services\FrontendCache;
use lenz\craft\essentials\services\LanguageRedirect;
use lenz\craft\essentials\services\MailEncoder;
use lenz\craft\essentials\services\RemoveDashboard;
use lenz\craft\utils\elementCache\ElementCache;

/**
 * Class Plugin
 *
 * @property ElementCache $elementCache
 * @property FrontendCache $frontendCache
 * @property LanguageRedirect $languageRedirect
 * @property MailEncoder $mailEncoder
 * @property RemoveDashboard $removeDashboard
 * @property services\i18n\I18N $i18n
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
      'frontendCache'    => FrontendCache::getInstance(),
      'languageRedirect' => LanguageRedirect::getInstance(),
      'mailEncoder'      => MailEncoder::getInstance(),
      'removeDashboard'  => RemoveDashboard::getInstance(),
      'i18n'             => services\i18n\I18N::class,
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
    return Craft::$app->view->renderTemplate('lenz-craft-essentials/_settings');
  }
}
