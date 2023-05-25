<?php

namespace lenz\craft\essentials\services\redirectNotFound\redirects;

use craft\elements\Entry;
use craft\web\Request;

/**
 * Class AbstractRedirect
 */
class CsvRedirect extends AbstractRedirect
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
   * @param string $url
   * @return string|null
   */
  protected function findTarget(string $url): ?string {
    $url = trim($url, '/');
    $handle = fopen($this->_fileName, 'r');
    $result = null;

    while (($data = fgetcsv($handle, 1000)) !== false) {
      if (count($data) >= 2 && trim($data[0], '/') == $url) {
        $result = trim($data[1]);
        break;
      }
    }

    fclose($handle);
    return $result;
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
