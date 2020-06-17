<?php

namespace lenz\craft\essentials;

use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;
use lenz\craft\essentials\fields\seo\SeoField;
use lenz\craft\utils\elementCache\ElementCache;
use lenz\craft\utils\foreignField\listeners\RegisterCpTemplates;
use Throwable;
use yii\base\Event;

/**
 * Class Plugin
 *
 * @property services\disabledLanguages\DisabledLanguages $disabledLanguages
 * @property ElementCache $elementCache
 * @property services\FrontendCache $frontendCache
 * @property services\gettext\Gettext $gettext
 * @property services\MailEncoder $mailEncoder
 * @property services\redirectLanguage\RedirectLanguage $redirectLanguage
 * @property services\redirectNotFound\RedirectNotFound $redirectNotFound
 * @property services\RemoveDashboard $removeDashboard
 * @property services\translations\Translations $translations
 * @property services\siteMap\SiteMapService $siteMap
 * @method Settings getSettings()
 */
class Plugin extends \craft\base\Plugin
{
  /**
   * @inheritDoc
   */
  public $hasCpSettings = true;

  /**
   * @inheritDoc
   */
  public $schemaVersion = '1.1.0';


  /**
   * @inheritDoc
   */
  public function init() {
    parent::init();

    $this->setComponents([
      'disabledLanguages' => services\disabledLanguages\DisabledLanguages::getInstance(),
      'elementCache'      => ElementCache::getInstance(),
      'frontendCache'     => services\FrontendCache::getInstance(),
      'gettext'           => services\gettext\Gettext::class,
      'mailEncoder'       => services\MailEncoder::getInstance(),
      'redirectLanguage'  => services\redirectLanguage\RedirectLanguage::getInstance(),
      'redirectNotFound'  => services\redirectNotFound\RedirectNotFound::getInstance(),
      'removeDashboard'   => services\RemoveDashboard::getInstance(),
      'removeStopWords'   => services\RemoveStopWords::getInstance(),
      'translations'      => services\translations\Translations::class,
      'siteMap'           => services\siteMap\SiteMapService::getInstance(),
    ]);

    RegisterCpTemplates::register();
    Event::on(
      Fields::class,
      Fields::EVENT_REGISTER_FIELD_TYPES,
      function(RegisterComponentTypesEvent $event) {
        $event->types[] = SeoField::class;
      }
    );

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
   * @throws Throwable
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
