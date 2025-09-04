<?php

namespace lenz\craft\essentials\services\eventBus\scopes;

/**
 * Class NamespaceScope
 */
class NamespaceScope extends AbstractScope
{
  /**
   * @param string $namespace
   * @param string $path
   */
  public function __construct(
    public readonly string $namespace,
    public readonly string $path,
  ) { }

  /**
   * @inheritDoc
   */
  public function findListeners(): array {
    $result = [];
    $files = new \RecursiveIteratorIterator(
      new \RecursiveDirectoryIterator($this->path)
    );

    /** @var \SplFileInfo $file */
    foreach ($files as $file) {
      if ($file->isDir()) continue;

      $className = $this->toClassName($file->getPathname());
      if (class_exists($className)) {
        array_push($result, ...ClassScope::toListeners($className));
      }
    }

    return $result;
  }


  // Private methods
  // ---------------

  /**
   * @param string $path
   * @return string|null
   */
  private function toClassName(string $path): string|null {
    if (!str_starts_with($path, $this->path)) {
      return null;
    }

    $className = str_replace(DIRECTORY_SEPARATOR, '\\',
      substr($path, strlen($this->path))
    );

    $splitAt = strrpos($className, '.');
    if ($splitAt !== false) {
      $className = substr($className, 0, $splitAt);
    }

    return $this->namespace . $className;
  }
}
