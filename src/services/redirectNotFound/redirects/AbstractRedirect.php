<?php

namespace lenz\craft\essentials\services\redirectNotFound\redirects;

use Craft;
use craft\web\Request;
use craft\web\Response;
use yii\base\ExitException;

/**
 * Class AbstractRedirect
 */
abstract class AbstractRedirect
{
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
  static public function getRedirects() {
    $result  = [];
    $csvFile = Craft::getAlias('@root/config/redirects.csv');
    if (file_exists($csvFile)) {
      $result[] = new CsvRedirect($csvFile);
    }

    return $result;
  }
}
