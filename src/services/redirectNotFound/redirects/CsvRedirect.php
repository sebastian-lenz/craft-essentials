<?php

namespace lenz\craft\essentials\services\redirectNotFound\redirects;

use Craft;
use craft\base\ElementInterface;
use craft\elements\Entry;
use craft\web\Request;
use Generator;
use lenz\craft\essentials\services\redirectNotFound\utils\ElementRoute;

/**
 * Class CsvRedirect
 */
class CsvRedirect extends AbstractRedirect implements AppendableRedirect, ElementRoutesRedirect
{
  /**
   * @var string
   */
  private string $_fileName;

  /**
   * @var array
   */
  const HANDLERS = [
    '#entry:' => 'resolveEntryHandler',
  ];


  /**
   * CsvRedirect constructor.
   *
   * @param string $fileName
   */
  public function __construct(string $fileName) {
    $this->_fileName = $fileName;
  }

  /**
   * @param string $origin
   * @param ElementInterface $target
   * @return void
   */
  public function append(string $origin, ElementInterface $target): void {
    $exists = file_exists($this->_fileName);
    $handle = fopen($this->_fileName, 'a');

    if (!$exists) {
      fputcsv($handle, ['source', 'target']);
    }

    fputcsv($handle, [$origin, "#entry:{$target->id}@{$target->siteId}"]);
    fclose($handle);
  }

  /**
   * @inheritDoc
   */
  public function delete(ElementRoute $route): void {
    $swapFile = $this->_fileName . '.swp';
    if (file_exists($swapFile)) {
      unlink($swapFile);
    }

    $handle = fopen($swapFile, 'w');
    foreach ($this->eachRow() as $index => $row) {
      if ($index == $route->originId && $row[0] == $route->url) {
        continue;
      }

      fputcsv($handle, $row);
    }

    fclose($handle);
    if (file_exists($this->_fileName)) {
      unlink($this->_fileName);
    }

    rename($swapFile, $this->_fileName);
  }

  /**
   * @inheritDoc
   */
  public function getElementRoutes(ElementInterface $element): array {
    $result = [];
    foreach ($this->eachRow() as $index => $data) {
      if (!str_starts_with($data[1], '#entry:')) {
        continue;
      }

      list($id, $siteId) = array_pad(
        explode('@', substr($data[1], 7), 2)
      , 2, null);

      if ($element->id != $id || $element->siteId != $siteId) {
        continue;
      }

      $result[] = new ElementRoute([
        'origin' => Craft::t('lenz-craft-essentials', 'Custom redirect'),
        'originId' => $index,
        'redirect' => $this,
        'uid' => md5( implode(';', [$this->_fileName, $index])),
        'url' => $data[0],
      ]);
    }

    return $result;
  }

  /**
   * @param Request $request
   * @return bool
   */
  public function redirect(Request $request): bool {
    $target = self::resolveHandler($this->findTarget($request->url));
    if (!empty($target)) {
      $this->sendRedirect($target);
      return true;
    }

    return false;
  }


  // Protected methods
  // -----------------

  /**
   * @return Generator
   */
  protected function eachRow(): Generator {
    $handle = fopen($this->_fileName, 'r');
    $index = 0;
    while (($data = fgetcsv($handle, 1000)) !== false) {
      if (count($data) >= 2) {
        yield $index => $data;
      }

      $index += 1;
    }

    fclose($handle);
  }

  /**
   * @param string $url
   * @return string|null
   */
  protected function findTarget(string $url): ?string {
    $url = trim($url, '/');
    foreach ($this->eachRow() as $data) {
      if (trim($data[0], '/') == $url) {
        return trim($data[1]);
      }
    }

    return null;
  }


  // Static methods
  // --------------

  /**
   * @param string $value
   * @return bool
   */
  public static function isHandler(string $value): bool {
    foreach (array_keys(self::HANDLERS) as $prefix) {
      if (str_starts_with($value, $prefix)) {
        return true;
      }
    }

    return false;
  }

  /**
   * @param string $value
   * @return string|null
   */
  static public function resolveEntryHandler(string $value): ?string {
    list($id, $siteId) = array_pad(explode('@', $value, 2), 2, null);
    $criteria = ['id' => $id];
    if (!empty($siteId)) {
      $criteria['siteId'] = $siteId;
    }

    $entry = Entry::findOne($criteria);
    return $entry?->url;
  }

  /**
   * @param mixed $value
   * @return string
   */
  static public function resolveHandler(mixed $value): string {
    if (!is_string($value) || empty($value)) {
      return '';
    }

    foreach (self::HANDLERS as $prefix => $callback) {
      if (str_starts_with($value, $prefix)) {
        return call_user_func([__CLASS__, $callback], substr($value, strlen($prefix)));
      }
    }

    return $value;
  }
}
