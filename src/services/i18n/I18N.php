<?php

namespace lenz\craft\essentials\services\i18n;

use Craft;
use craft\models\Site;
use Gettext\Merge;
use Gettext\Translation;
use lenz\craft\essentials\services\i18n\sources\AbstractSource;
use lenz\contentfield\fields\ContentField;
use yii\base\Component;
use yii\helpers\VarDumper;

/**
 * Class I18N
 */
class I18N extends Component
{
  /**
   * @var AbstractSource[]
   */
  private $_sources;

  /**
   * Triggered wh
   */
  const EVENT_REGISTER_SOURCES = 'registerSources';


  /**
   * @param string $handle
   * @param AbstractSource $source
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
  public function extract() {
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
  public function getSources() {
    if (!isset($this->_sources)) {
      $this->_sources = [
        'cp-fields'   => new sources\CpFieldsSource(),
        'cp-elements' => new sources\CpElementsSource(),
        'modules'     => new sources\ModulesSource(),
        'templates'   => new sources\TemplatesSource(),
      ];

      if (class_exists(ContentField::class)) {
        $this->_sources['cp-content-field'] = new sources\ContentFieldSource();
      }

      $this->trigger(self::EVENT_REGISTER_SOURCES);
    }

    return $this->_sources;
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


  // Private methods
  // ---------------

  private function compileSiteTranslations(Site $site) {
    $path = $this->getSiteTranslationPath($site);
    $source = implode(DIRECTORY_SEPARATOR, [$path, 'site.po']);
    $target = implode(DIRECTORY_SEPARATOR, [$path, 'site.php']);
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
  private function getSiteTranslationPath(Site $site) {
    $path = Craft::getAlias(implode(
      DIRECTORY_SEPARATOR,
      ['@root', 'translations', $site->language]
    ));

    if (!file_exists($path)) {
      mkdir($path);
    }

    return $path;
  }

  /**
   * @param Translations $translations
   * @param Site $site
   * @param string $path
   * @return Translations
   */
  private function merge(Translations $translations, Site $site, string $path) {
    $translations = $translations->clone();

    foreach (scandir($path) as $existingName) {
      $existingPath = implode(DIRECTORY_SEPARATOR, [$path, $existingName]);
      if (preg_match('/\.php$/', $existingName)) {
        $this->mergePhp($translations, $site, $existingPath);
      } elseif (preg_match('/\.po$/', $existingName)) {
        $this->mergePo($translations, $site, $existingPath);
      }
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
    $translations->mergeWith($existing, Merge::TRANSLATION_OVERRIDE);
  }

  /**
   * @param Translations $translations
   */
  private function storeTranslations(Translations $translations) {
    $sites = Craft::$app->getSites()->getAllSites();
    foreach ($sites as $site) {
      $this->storeSiteTranslations($translations, $site);
    }
  }

  /**
   * @param Translations $translations
   * @param Site $site
   */
  private function storeSiteTranslations(Translations $translations, Site $site) {
    $path = $this->getSiteTranslationPath($site);
    $this->merge($translations, $site, $path)
      ->setLanguage($site->language)
      ->toPoFile(implode(DIRECTORY_SEPARATOR, [$path, 'site.po']));
  }
}
