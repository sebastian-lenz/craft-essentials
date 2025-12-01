<?php

namespace lenz\craft\essentials\services\dev;

use craft\helpers\App;
use lenz\craft\essentials\helpers\Arr;

/**
 * Class ProjectEnums
 */
class ProjectEnums
{
  /** @var string */
  public readonly string $moduleName;

  /** @var string */
  public readonly string $modulePath;

  /** @var string */
  public string $outPath = 'models/enums';


  /**
   * @param string|null $moduleName
   * @param string|null $modulePath
   */
  public function __construct(string|null $moduleName = null, string|null $modulePath = null) {
    if (is_null($moduleName)) {
      $moduleName = Arr::first(array_keys(\Craft::$app->getModules()));
    }

    if (empty($moduleName)) {
      throw new \Exception('Could not find app module');
    }

    $modulePath = App::parseEnv("@$moduleName");
    if (empty($modulePath) || !file_exists($modulePath)) {
      throw new \Exception("Could not find module path for module `$moduleName`");
    }

    $this->moduleName = $moduleName;
    $this->modulePath = $modulePath;
  }

  /**
   * @return string
   */
  public function getNamespace(): string {
    return implode('\\', [$this->moduleName, ...explode(DIRECTORY_SEPARATOR, $this->modulePath)]);
  }

  /**
   * @return string
   */
  public function getOutDir(): string {
    $outDir = implode(DIRECTORY_SEPARATOR, [$this->modulePath, $this->outPath]);
    if (!file_exists($outDir)) {
      mkdir($outDir, 0666, true);
    }

    return $outDir;
  }

  /**
   * @return ProjectEnums\Writer[]
   */
  public function getWriters(): array {
    $app = \Craft::$app;

    return [
      new ProjectEnums\Writer($this, 'EntryType', $app->getEntries()->getAllEntryTypes()),
      new ProjectEnums\Writer($this, 'Section', $app->getEntries()->getAllSections()),
      new ProjectEnums\Writer($this, 'Site', $app->getSites()->getAllSites()),
      new ProjectEnums\Writer($this, 'Volume', $app->getVolumes()->getAllVolumes()),
    ];
  }
}
