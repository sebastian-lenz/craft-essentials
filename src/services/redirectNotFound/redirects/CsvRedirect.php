<?php

namespace lenz\craft\essentials\services\redirectNotFound\redirects;

use craft\web\Request;

/**
 * Class AbstractRedirect
 */
class CsvRedirect extends AbstractRedirect
{
  /**
   * @var string
   */
  private $_fileName;


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
    $result = null;
    $url    = trim($request->url, '/');
    $handle = fopen($this->_fileName, 'r');

    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
      if (count($data) >= 2 && trim($data[0], '/') == $url) {
        $result = trim($data[1]);
        break;
      }
    }

    fclose($handle);
    if (!is_null($result)) {
      $this->sendRedirect($result);
      return true;
    }

    return false;
  }
}
