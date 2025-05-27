<?php

namespace lenz\craft\essentials\services\passwordPolicy\helpers;

use Craft;
use Illuminate\Support\Collection;
use lenz\craft\essentials\Plugin;

/**
 * class PasswordPolicy
 */
class PasswordPolicy
{
  /**
   * Method to generate the validation message.
   * @return string
   */
  static public function getMessage(): string {
    $settings = Plugin::getInstance()->getSettings()->passwordPolicy;
    $messages = self::createMessages()
      ->reject(fn(string $value, string $key) => $settings->{$key} === false);

    $lastMessage = $messages->pop();
    if (!count($messages)) {
      return $lastMessage;
    }

    return implode(' ', [
      $messages->join(', '),
      Craft::t('lenz-craft-essentials', 'and'),
      $lastMessage
    ]);
  }

  /**
   * @return string
   */
  static public function getPattern(): string {
    $settings = Plugin::getInstance()->getSettings()->passwordPolicy;
    $pattern = self::createPatterns()
      ->reject(fn(string $value, string $key) => $settings->{$key} === false)
      ->implode('');

    return '/^' . $pattern . '/';
  }


  // Private methods
  // ---------------

  /**
   * @return Collection
   */
  static private function createMessages(): Collection {
    return collection::make([
      'requireCases' => Craft::t('lenz-craft-essentials', 'a lowercase character, an uppercase character'),
      'requireNumbers' => Craft::t('lenz-craft-essentials', 'a number'),
      'requireSymbols' => Craft::t('lenz-craft-essentials', 'a special character'),
    ]);
  }

  /**
   * @return Collection
   */
  static private function createPatterns(): Collection {
    return Collection::make([
      'requireCases' => '(?=.*[a-z])(?=.*[A-Z])',
      'requireNumbers' => '(?=.*[0-9])',
      'requireSymbols' => '(?=.*[!@#\$%\^&\*])',
    ]);
  }
}
