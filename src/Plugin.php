<?php

namespace lenz\craft\essentials;

use Craft;
use lenz\craft\utils\elementCache\ElementCache;
use lenz\craft\utils\foreignField\listeners\RegisterCpTemplates;
use Throwable;
use yii\BaseYii;

/**
 * Class Plugin
 *
 * @property services\disabledLanguages\DisabledLanguages $disabledLanguages
 * @property ElementCache $elementCache
 * @property services\eventBus\EventBus $eventBus
 * @property services\frontendCache\FrontendCacheService $frontendCache
 * @property services\gettext\Gettext $gettext
 * @property services\redirectLanguage\RedirectLanguage $redirectLanguage
 * @property services\redirectNotFound\RedirectNotFound $redirectNotFound
 * @property services\RemoveStopWords $removeStopWords
 * @property services\siteMap\SiteMapService $siteMap
 * @property services\tables\Tables $tables
 * @property services\translations\Translations $translations
 * @property services\webp\Webp $webp
 * @method Settings getSettings()
 */
class Plugin extends \craft\base\Plugin
{
  /**
   * @inheritDoc
   */
  public bool $hasCpSettings = true;

  /**
   * @inheritDoc
   */
  public string $schemaVersion = '1.2.0';


  /**
   * @inheritDoc
   */
  public function init(): void {
    parent::init();

    $this->setComponents([
      'elementCache' => ElementCache::getInstance(),
      'eventBus' => services\eventBus\EventBus::getInstance()->addClass(
        services\cp\CpHelpers::class,
        services\disabledLanguages\DisabledLanguages::class,
        services\frontendCache\FrontendCacheService::class,
        services\imageCompressor\ImageCompressor::class,
        services\imagePlaceholder\ImagePlaceholder::class,
        services\imageSharpener\ImageSharpener::class,
        services\loginSecurity\LoginSecurity::class,
        services\MailEncoder::class,
        services\malwareScanner\MalwareScanner::class,
        services\passwordPolicy\PasswordPolicy::class,
        services\redirectLanguage\RedirectLanguage::class,
        services\redirectNotFound\RedirectNotFound::class,
        services\RemoveStopWords::class,
        services\tables\Tables::class,
        services\webp\Webp::class,
      ),
      'disabledLanguages' => services\disabledLanguages\DisabledLanguages::class,
      'frontendCache'     => services\frontendCache\FrontendCacheService::class,
      'gettext'           => services\gettext\Gettext::class,
      'redirectLanguage'  => services\redirectLanguage\RedirectLanguage::class,
      'redirectNotFound'  => services\redirectNotFound\RedirectNotFound::class,
      'removeStopWords'   => services\RemoveStopWords::class,
      'siteMap'           => services\siteMap\SiteMapService::class,
      'tables'            => services\tables\Tables::class,
      'translations'      => services\translations\Translations::class,
      'webp'              => services\webp\Webp::class,
    ]);

    Craft::$app->view->registerTwigExtension(new twig\Extension());
    RegisterCpTemplates::register();

    BaseYii::$container->set(\craft\fields\linktypes\Url::class, [
      'class' => services\cp\linktypes\Url::class,
    ]);
  }

  /**
   * @inheritdoc
   */
  public function createSettingsModel(): Settings {
    return new Settings();
  }


  // Protected methods
  // -----------------

  /**
   * @inheritdoc
   * @throws Throwable
   */
  protected function settingsHtml(): ?string {
    return Craft::$app->view->renderTemplate(
      'lenz-craft-essentials/_settings/index',
      [
        'settings' => $this->getSettings(),
      ]
    );
  }
}
