<?php

namespace lenz\craft\essentials;

use Craft;
use craft\base\Model;

/**
 * Class Settings
 */
class Settings extends Model
{
  /**
   * @var string[]
   */
  public $cachedRoutes = ['templates/render'];

  /**
   * @var string[]
   */
  public $disabledLanguages = [];

  /**
   * @var bool
   */
  public $enableLanguageRedirect = true;

  /**
   * @var bool
   */
  public $enableImageSharpening = false;

  /**
   * @var bool
   */
  public $enableWebp = false;


  /**
   * @inheritDoc
   */
  public function afterValidate() {
    $this->enableLanguageRedirect = !!$this->enableLanguageRedirect;
    $this->enableImageSharpening = !!$this->enableImageSharpening;
    $this->enableWebp = !!$this->enableWebp;

    $this->cachedRoutes = is_array($this->cachedRoutes)
      ? $this->cachedRoutes
      : [];

    $this->disabledLanguages = is_array($this->disabledLanguages)
      ? $this->disabledLanguages
      : [];
  }

  /**
   * @return array
   */
  public function getAllLanguages(): array {
    $sites = Craft::$app->getSites()->getAllSites();
    $languages = [];
    $locales = Craft::$app->getI18n()->getAllLocales();

    foreach ($sites as $site) {
      $label = $site->language;
      foreach ($locales as $locale) {
        if ($locale->id === $site->language) {
          $label = $locale->getDisplayName();
          break;
        }
      }

      $languages[] = [
        'label' => ucfirst($label),
        'value' => $site->language,
      ];
    }

    return $languages;
  }
}
