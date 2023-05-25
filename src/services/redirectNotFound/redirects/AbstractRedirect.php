<?php

namespace lenz\craft\essentials\services\redirectNotFound\redirects;

use craft\web\Request;
use craft\web\Response;

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
  protected function sendRedirect(string $url): void {
    $response = new Response();
    $response->redirect($url, 301)->send();
    die();
  }
}
