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
 * @method Settings getSettings()
 */
class Plugin extends \craft\base\Plugin
{
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
    ]);

    Craft::$app->view->registerTwigExtension(new twig\Extension());
  }

  /**
   * @inheritdoc
   */
  public function createSettingsModel() {
    return new Settings();
  }
}
