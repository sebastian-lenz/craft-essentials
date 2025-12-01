<?php

namespace lenz\craft\essentials\services\dev\ProjectEnums;

use craft\models\EntryType;
use craft\models\Section;
use craft\models\Site;
use craft\models\Volume;
use lenz\craft\essentials\services\dev\ProjectEnums;

/**
 * Class Writer
 */
class Writer
{
  /**
   * @var string
   */
  const CASE_REGEXP = '/^\s*case .*\n/m';


  /**
   * @param ProjectEnums $manager
   * @param string $name
   * @param Array<EntryType|Section|Site|Volume> $items
   */
  public function __construct(
    public readonly ProjectEnums $manager,
    public readonly string       $name,
    public readonly array        $items,
  ) { }

  /**
   * @return void
   */
  public function write(): void {
    $wrapper = $this->getWrapper();
    file_put_contents($this->getFileName(), implode("\n", [
      $wrapper[0],
      ...$this->createCases(),
      $wrapper[1],
    ]));
  }


  // Private methods
  // ---------------

  /**
   * @return string[]
   */
  private function createCases(): array {
    return array_map(fn(EntryType|Section|Site|Volume $item) =>
      '  case ' . ucfirst($item->handle) . ' = "' . $item->handle . '";',
    $this->items);
  }

  /**
   * @return array
   */
  private function getDefaultWrapper(): array {
    $namespace = $this->manager->getNamespace();

    return [
      implode("\n", [
        '<' . '?php',
        '',
        "namespace $namespace;",
        '',
        "use lenz\\craft\\essentials\\structs\\enum\\{$this->name}Trait;",
        '',
        '/**',
        " * Enum $this->name",
        ' */',
        "enum $this->name: string",
        '{',
        "  use {$this->name}Trait;",
        '',
      ]),
      '}'
    ];
  }

  /**
   * @return string
   */
  private function getFileName(): string {
    return implode(DIRECTORY_SEPARATOR, [$this->manager->getOutDir(), "$this->name.php"]);
  }

  /**
   * @param string $fileName
   * @return array
   */
  private function getFileWrapper(string $fileName): array {
    $contents = file_get_contents($fileName);
    if (!preg_match(self::CASE_REGEXP, $contents, $matches, PREG_OFFSET_CAPTURE)) {
      throw new \Exception('Could not find enum cases in `$fileName`');
    }

    $offset = intval($matches[0][1]);
    $contents = preg_replace(self::CASE_REGEXP, '', $contents);

    return [
      substr($contents, 0, $offset),
      substr($contents, $offset)
    ];
  }

  /**
   * @return array
   */
  private function getWrapper(): array {
    $fileName = $this->getFileName();

    if (file_exists($fileName)) {
      return $this->getFileWrapper($fileName);
    } else {
      return $this->getDefaultWrapper();
    }
  }
}
