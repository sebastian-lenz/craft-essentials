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
   * @var int
   */
  const DEFAULT_REDIRECT_CODE = 301;

  /**
   * @var int[]
   */
  const REDIRECT_CODES = [301, 302];


  /**
   * @param Request $request
   * @return bool
   */
  abstract public function redirect(Request $request) : bool;


  // Protected methods
  // -----------------

  /**
   * @param string $url
   * @param int $code
   */
  protected function sendRedirect(string $url, int $code = self::DEFAULT_REDIRECT_CODE): void {
    if (!in_array($code, self::REDIRECT_CODES)) {
      $code = self::DEFAULT_REDIRECT_CODE;
    }

    $response = new Response();
    $response->redirect($url, $code)->send();
    die();
  }
}
