<?php

namespace lenz\craft\essentials\services\redirectNotFound\redirects;

use Craft;
use craft\web\Request;
use craft\web\Response;

/**
 * Class AbstractRedirect
 */
abstract class AbstractRedirect
{
  /**
   * @var array
   */
  const KNOWN_CSV_PATHS = [
    '@root/config/redirects.csv',
    '@storage/tables/redirects.csv',
  ];


  /**
   * @param Request $request
   * @return bool
   */
  abstract public function redirect(Request $request) : bool;


  // Protected methods
  // -----------------

  /**
   * @param string $url
   */
  protected function sendRedirect(string $url) {
    $response = new Response();
    $response->redirect($url, 301)->send();
    die();
  }


  // Static methods
  // --------------

  /**
   * @return AbstractRedirect[]
   */
  static public function getRedirects(): array {
    $result  = [
      new SiteMapRedirect(),
    ];

    foreach (self::KNOWN_CSV_PATHS as $path) {
      $path = Craft::getAlias($path);
      if (file_exists($path)) {
        $result[] = new CsvRedirect($path);
      }
    }

    return $result;
  }
}
