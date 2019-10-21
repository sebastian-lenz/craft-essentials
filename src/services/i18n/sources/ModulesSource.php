<?php

namespace lenz\craft\essentials\services\i18n\sources;

use Craft;
use craft\helpers\ArrayHelper;
use Gettext\Extractors\PhpCode;
use lenz\craft\essentials\services\i18n\Translations;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

/**
 * Class ModulesSource
 */
class ModulesSource extends AbstractSource
{
  /**
   * @param Translations $translations
   */
  public function extract(Translations $translations) {
    foreach ($this->getModules() as $module => $moduleClass) {
      $path = Craft::getAlias(implode(DIRECTORY_SEPARATOR, ['@root', $module]));
      if (!file_exists($path)) {
        echo sprintf('\nError: Could not find path to module `%s`.', $module);
        continue;
      }

      $this->extractModule($translations, $path);
    }
  }

  // Private methods
  // ---------------

  /**
   * @param Translations $translations
   * @param string $path
   */
  private function extractFile(Translations $translations, string $path) {
    PhpCode::fromFile($path, $translations, [
      'functions' => [
        't' => 'pgettext',
      ],
    ]);
  }

  /**
   * @param Translations $translations
   * @param string $basePath
   */
  private function extractModule(Translations $translations, string $basePath) {
    $dirIterator = new RecursiveDirectoryIterator($basePath);
    $iterator    = new RecursiveIteratorIterator($dirIterator);

    foreach ($iterator as $path) {
      if (!preg_match('/\.php$/', $path)) {
        continue;
      }

      $this->extractFile($translations, $path);
    }
  }

  /**
   * @return array
   */
  private function getModules() {
    try {
      $config = Craft::$app->config->getConfigFromFile('app');
      return ArrayHelper::getValue($config, ['modules'], []);
    } catch (Throwable $error) {
      echo "\nError: Could not load module configuration.";
    }

    return [];
  }
}
