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
  public $dataTables = [];

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
  public $enableImageCompressor = true;

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
    $this->enableImageCompressor = !!$this->enableImageCompressor;
    $this->enableImageSharpening = !!$this->enableImageSharpening;
    $this->enableWebp = !!$this->enableWebp;

    $this->dataTables = self::parseList($this->dataTables);

    $this->cachedRoutes = is_array($this->cachedRoutes)
      ? $this->cachedRoutes
      : [];

    $this->disabledLanguages = is_array($this->disabledLanguages)
      ? $this->disabledLanguages
      : [];
  }

  /**
   * @return array
   * @noinspection PhpUnused (Template helper)
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


  // Static methods
  // --------------

  /**
   * @param mixed $value
   * @return array
   */
  static function parseList($value): array {
    $list = is_array($value) ? $value : explode("\n", $value);
    return array_filter(array_map(function($value) {
      return trim($value);
    }, $list));
  }
}
