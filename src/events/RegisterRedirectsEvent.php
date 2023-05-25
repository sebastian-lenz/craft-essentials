<?php

namespace lenz\craft\essentials\events;

use craft\helpers\App;
use lenz\craft\essentials\services\redirectNotFound\redirects\AbstractRedirect;
use lenz\craft\essentials\services\redirectNotFound\redirects\CsvRedirect;
use lenz\craft\essentials\services\redirectNotFound\redirects\SiteMapRedirect;
use lenz\craft\essentials\services\redirectNotFound\redirects\UriHistoryRedirect;
use yii\base\Event;

/**
 * Class RegisterRedirectsEvent
 */
class RegisterRedirectsEvent extends Event
{
  /**
   * @var AbstractRedirect[]
   */
  public array $redirects;

  /**
   * @var array
   */
  const KNOWN_CSV_PATHS = [
    '@root/config/redirects.csv',
    '@storage/tables/redirects.csv',
  ];


  // Static methods
  // --------------

  /**
   * @return RegisterRedirectsEvent
   */
  static public function create(): RegisterRedirectsEvent {
    return new RegisterRedirectsEvent([
      'redirects' => self::createDefaultRedirects(),
    ]);
  }

  /**
   * @return AbstractRedirect[]
   */
  static public function createDefaultRedirects(): array {
    $result  = [
      new UriHistoryRedirect(),
      new SiteMapRedirect(),
    ];

    foreach (self::KNOWN_CSV_PATHS as $path) {
      $path = App::parseEnv($path);
      if (file_exists($path)) {
        $result[] = new CsvRedirect($path);
      }
    }

    return $result;
  }
}
