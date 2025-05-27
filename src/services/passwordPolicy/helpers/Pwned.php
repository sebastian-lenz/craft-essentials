<?php

namespace lenz\craft\essentials\services\passwordPolicy\helpers;

use Craft;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;

/**
 * Class Pwned
 */
class Pwned
{
  /**
   * @var string
   */
  public const PWNED_ENDPOINT = 'https://api.pwnedpasswords.com/range/';


  /**
   * @param string $password
   * @return bool|null
   */
  static public function isPwned(string $password): ?bool {
    $hash = strtoupper(sha1($password));
    $prefix = substr($hash, 0, 5);
    $suffix = substr($hash, 5);

    $endpoint = self::PWNED_ENDPOINT . $prefix;

    try {
      $client = Craft::createGuzzleClient([
        'headers' => ['Add-Padding' => 'true'],
      ]);

      $response = $client->request('GET', $endpoint);
      $passwords = Collection::make(explode(
          "\r\n",
          $response->getBody()->getContents()
        ))
        ->map(fn($password) => strtok($password, ':'))
        ->filter(fn($password) => $suffix === $password);

      return $passwords->count() > 0;
    } catch (GuzzleException) {
      return false;
    }
  }
}
