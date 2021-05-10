<?php

namespace lenz\craft\essentials\services\gettext\sources;

use Craft;
use craft\helpers\ArrayHelper;
use Exception;
use lenz\craft\essentials\services\gettext\Gettext;
use lenz\craft\essentials\services\gettext\utils\PhpFunctionsScanner;
use lenz\craft\essentials\services\gettext\utils\Translations;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

/**
 * Class ModulesSource
 */
class ModulesSource extends AbstractSource
{
  /**
   * @inheritDoc
   * @throws Exception
   */
  public function extract(Translations $translations) {
    foreach (array_keys($this->getModules()) as $module) {
      $path = $this->getModulePath($module);
      if (is_null($path)) {
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
   * @throws Exception
   */
  private function extractFile(Translations $translations, string $path) {
    Gettext::printSource('file', $path);

    $scanner = new PhpFunctionsScanner(file_get_contents($path));
    $scanner->save($translations, [
      'constants' => [],
      'file'      => $path,
      'functions' => [
        't' => 'pgettext',
      ],
    ]);
  }

  /**
   * @param Translations $translations
   * @param string $basePath
   * @throws Exception
   */
  private function extractModule(Translations $translations, string $basePath) {
    $dirIterator = new RecursiveDirectoryIterator($basePath);
    $iterator    = new RecursiveIteratorIterator($dirIterator);

    foreach ($iterator as $path) {
      if (!preg_match('/\.php$/', $path) || $this->_gettext->isFileExcluded($path)) {
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

  /**
   * @param string $module
   * @return string|null
   */
  private function getModulePath(string $module): ?string {
    $path = Craft::getAlias('@' . $module);
    if (file_exists($path)) {
      return $path;
    }

    $path = Craft::getAlias(implode(DIRECTORY_SEPARATOR, ['@root', $module]));
    if (file_exists($path)) {
      return $path;
    }

    return null;
  }
}
