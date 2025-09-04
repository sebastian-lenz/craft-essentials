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
  public array $cachedRoutes = ['templates/render'];

  /**
   * @var string[]
   */
  public array $dataTables = [];

  /**
   * @var string[]
   */
  public array $disabledLanguages = [];

  /**
   * @var bool
   */
  public bool $enableLanguageRedirect = true;

  /**
   * @var bool
   */
  public bool $enableImageCompressor = true;

  /**
   * @var bool
   */
  public bool $enabledImagePlaceholders = false;

  /**
   * @var bool
   */
  public bool $enableImageSharpening = false;

  /**
   * @var bool
   */
  public bool $enableWebp = false;

  /**
   * @var bool
   */
  public bool $ensureSiteSegment = true;

  /**
   * @var string[]
   */
  public array $iconClasses = [];

  /**
   * @var string
   */
  public string $malwareCommand = '';

  /**
   * @var services\passwordPolicy\Settings
   */
  public services\passwordPolicy\Settings $passwordPolicy;

  /**
   * @var array
   */
  const LISTS = ['dataTables', 'iconClasses'];
  const MODELS = ['passwordPolicy' => services\passwordPolicy\Settings::class];


  /**
   * @inheritDoc
   */
  public function __construct(mixed $config = []) {
    parent::__construct($config);

    $this->passwordPolicy = new services\passwordPolicy\Settings();
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

  /**
   * @inheritDoc
   */
  public function setAttributes($values, $safeOnly = true): void {
    foreach (self::LISTS as $name) {
      if (array_key_exists($name, $values)) {
        $values[$name] = self::parseList($values[$name]);
      }
    }

    foreach (self::MODELS as $name => $modelClass) {
      if (array_key_exists($name, $values)) {
        $values[$name] = new $modelClass($values[$name]);
      }
    }

    parent::setAttributes($values, $safeOnly);
  }

  /**
   * @inheritDoc
   */
  public function toArray(array $fields = [], array $expand = [], $recursive = true): array {
    $result = parent::toArray($fields, $expand, $recursive);

    foreach ($result as $key => $value) {
      if (is_array($value)) {
        $result[$key] = (object)$value;
      }
    }

    return $result;
  }


  // Static methods
  // --------------

  /**
   * @param mixed $value
   * @return array
   */
  static function parseList(mixed $value): array {
    $list = is_array($value) ? $value : explode("\n", $value);

    return array_filter(array_map(
      fn($value) => trim($value),
      $list
    ));
  }
}
