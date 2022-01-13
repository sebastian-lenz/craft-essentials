<?php

namespace lenz\craft\essentials\services\gettext;

use Craft;
use craft\helpers\FileHelper;
use craft\models\Site;
use Gettext\Merge;
use Gettext\Translation;
use lenz\craft\essentials\services\gettext\sources\AbstractSource;
use lenz\contentfield\fields\ContentField;
use lenz\craft\essentials\services\gettext\utils\Translations;
use Yii;
use yii\base\Component;
use yii\helpers\VarDumper;

/**
 * Class Gettext
 */
class Gettext extends Component
{
  /**
   * @var string
   */
  public $basePath;

  /**
   * @var string[]
   */
  public $excludeFiles = [];

  /**
   * @var string[]
   */
  public $excludeLanguages = [];

  /**
   * @var AbstractSource[]
   */
  private $_sources;

  /**
   * Triggered wh
   */
  const EVENT_REGISTER_SOURCES = 'registerSources';


  /**
   * Gettext constructor.
   *
   * @param array $config
   */
  public function __construct($config = []) {
    parent::__construct($config);

    $this->basePath = Yii::getAlias('@root');
  }

  /**
   * @param string $handle
   * @param AbstractSource $source
   * @noinspection PhpUnused Public API
   */
  public function addSource(string $handle, AbstractSource $source) {
    if (!isset($this->_sources)) {
      $this->getSources();
    }

    $this->_sources[$handle] = $source;
  }

  /**
   * @return void
   */
  public function compile() {
    $sites = Craft::$app->getSites()->getAllSites();
    foreach ($sites as $site) {
      $this->compileSiteTranslations($site);
    }
  }

  /**
   * @return Translations
   */
  public function extract(): Translations {
    $translations = new Translations();
    foreach ($this->getSources() as $source) {
      $source->extract($translations);
    }

    /** @var Translation $translation */
    foreach ($translations->getArrayCopy() as $key => $translation) {
      if ($translation->getContext() == 'site') {
        $translations->offsetUnset($key);
        $translations->append($translation->getClone(''));
      }
    }

    $this->storeTranslations($translations);
    return $translations;
  }

  /**
   * @return AbstractSource[]
   */
  public function getSources(): array {
    if (!isset($this->_sources)) {
      $this->_sources = [
        'cp-fields'   => new sources\CpFieldsSource($this),
        'cp-element-indexes' => new sources\CpElementIndexesSource($this),
        'cp-elements' => new sources\CpElementsSource($this),
        'modules'     => new sources\ModulesSource($this),
        'templates'   => new sources\TemplatesSource($this),
      ];

      if (class_exists(ContentField::class)) {
        $this->_sources['cp-content-field'] = new sources\ContentFieldSource($this);
      }

      $this->trigger(self::EVENT_REGISTER_SOURCES);
    }

    return $this->_sources;
  }

  /**
   * @param string $path
   * @return bool
   */
  public function isFileExcluded(string $path): bool {
    $result = !FileHelper::filterPath($path, [
      'basePath' => $this->basePath,
      'except' => $this->excludeFiles,
    ]);

    if ($result) {
      echo ' - Ignoring ' . $path . "`\n";
    }

    return $result;
  }

  /**
   * @param string $handle
   */
  public function removeSource(string $handle) {
    if (!isset($this->_sources)) {
      $this->getSources();
    }

    unset($this->_sources[$handle]);
  }

  /**
   * @param array $options
   * @return $this
   */
  public function setOptions(array $options): Gettext {
    Yii::configure($this, $options);
    return $this;
  }


  // Private methods
  // ---------------

  /**
   * @param Site $site
   */
  private function compileSiteTranslations(Site $site) {
    $source = $this->getSiteTranslationSource($site);
    $target = implode(DIRECTORY_SEPARATOR, [
      $this->getSiteTranslationPath($site),
      'site.php'
    ]);

    if (!file_exists($source) || !is_readable($source)) {
      return;
    }

    $translations = new Translations();
    $translations->addFromPoFile($source);
    $phpArray = [];

    /** @var Translation $translation */
    foreach ($translations as $translation) {
      $value = $translation->getTranslation();
      if (!empty($value)) {
        $phpArray[$translation->getOriginal()] = $value;
      }
    }

    ksort($phpArray);
    file_put_contents(
      $target,
      '<?php return ' . VarDumper::export($phpArray) . ';'
    );
  }

  /**
   * @param Site $site
   * @return string
   */
  private function getSiteTranslationPath(Site $site): string {
    $path = Craft::getAlias(implode(
      DIRECTORY_SEPARATOR,
      ['@root', 'translations', $site->language]
    ));

    if (!file_exists($path)) {
      mkdir($path, 0777, true);
    }

    return $path;
  }

  /**
   * @param Site $site
   * @return string
   */
  private function getSiteTranslationSource(Site $site): string {
    return Craft::getAlias(implode(
      DIRECTORY_SEPARATOR,
      ['@root', 'translations', $site->language . '.po']
    ));
  }

  /**
   * @param Translations $translations
   * @param Site $site
   * @param string $path
   * @return Translations
   */
  private function merge(Translations $translations, Site $site, string $path): Translations {
    $translations = $translations->clone();

    foreach (scandir($path) as $existingName) {
      $existingPath = implode(DIRECTORY_SEPARATOR, [$path, $existingName]);
      if (preg_match('/\.php$/', $existingName)) {
        $this->mergePhp($translations, $site, $existingPath);
      } elseif (preg_match('/\.po$/', $existingName)) {
        $this->mergePo($translations, $site, $existingPath);
      }
    }

    $source = $this->getSiteTranslationSource($site);
    if (file_exists($source)) {
      $this->mergePo($translations, $site, $source);
    }

    return $translations;
  }

  /**
   * @param Translations $translations
   * @param Site $site
   * @param string $path
   */
  private function mergePhp(Translations $translations, Site $site, string $path) {
    $messages = require $path;

    foreach ($messages as $original => $value) {
      foreach ($translations as $translation) {
        if ($translation->getOriginal() == $original) {
          $translation->setTranslation($value);
        }
      }
    }
  }

  /**
   * @param Translations $translations
   * @param Site $site
   * @param string $path
   */
  private function mergePo(Translations $translations, Site $site, string $path) {
    $existing = new Translations();
    $existing->addFromPoFile($path);
    $translations->mergeWith($existing, Merge::TRANSLATION_OVERRIDE | Merge::REFERENCES_OURS);
  }

  /**
   * @param Translations $translations
   */
  private function storeTranslations(Translations $translations) {
    $sites = Craft::$app->getSites()->getAllSites();
    foreach ($sites as $site) {
      if (in_array($site->language, $this->excludeLanguages)) {
        continue;
      }

      $this->storeSiteTranslations($translations, $site);
    }
  }

  /**
   * @param Translations $translations
   * @param Site $site
   */
  private function storeSiteTranslations(Translations $translations, Site $site) {
    $this->merge($translations, $site, $this->getSiteTranslationPath($site))
      ->setLanguage($site->language)
      ->toPoFile($this->getSiteTranslationSource($site));
  }

  // Static methods
  // --------------

  /**
   * @param string $type
   * @param string $name
   */
  static function printSource(string $type, string $name) {
    echo ' - ' . ucfirst($type) . ' `' . $name . "`\n";
  }
}
