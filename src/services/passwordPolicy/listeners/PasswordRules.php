<?php

namespace lenz\craft\essentials\services\passwordPolicy\listeners;

use craft\events\DefineRulesEvent;
use lenz\craft\essentials\Plugin;
use lenz\craft\essentials\services\passwordPolicy\helpers\PasswordPolicy;
use lenz\craft\essentials\services\passwordPolicy\validators\PwnedValidator;

/**
 * Class PasswordRules
 */
class PasswordRules
{
  /**
   * @param DefineRulesEvent $event
   * @return void
   */
  static public function onDefineRules(DefineRulesEvent $event): void {
    $settings = Plugin::getInstance()->getSettings()->passwordPolicy;

    if ($settings->usePwnedValidator) {
      array_unshift($event->rules, [['password', 'newPassword'], PwnedValidator::class]);
    }

    array_unshift($event->rules, [
      ['password', 'newPassword'],
      'match',
      'pattern' => PasswordPolicy::getPattern(),
      'message' => \Craft::t(
        'lenz-craft-essentials',
        'Your password must contain at least one of each of the following: {rules}.',
        ['rules' => PasswordPolicy::getMessage()]
      ),
    ]);

    if ($settings->maxLength > $settings->minLength) {
      array_unshift($event->rules, [
        ['password', 'newPassword'],
        'string',
        'max' => $settings->maxLength,
        'tooLong' => \Craft::t(
          'lenz-craft-essentials',
          'Password can maximum contain {max} characters.',
          ['max' => $settings->maxLength]
        ),
      ]);
    }

    array_unshift($event->rules, [
      ['password', 'newPassword'],
      'string',
      'min' => $settings->minLength,
      'tooShort' => \Craft::t(
        'lenz-craft-essentials',
        'Password must contain at least {min} characters.',
        ['min' => $settings->minLength]
      ),
    ]);
  }
}
