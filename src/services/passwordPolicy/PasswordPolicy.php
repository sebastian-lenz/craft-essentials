<?php

namespace lenz\craft\essentials\services\passwordPolicy;

use craft\base\Model;
use craft\elements\User;
use craft\events\DefineRulesEvent;
use lenz\craft\essentials\Plugin;
use lenz\craft\essentials\services\eventBus\On;
use lenz\craft\essentials\services\passwordPolicy\validators\PwnedValidator;

/**
 * Class PasswordRules
 */
class PasswordPolicy
{
  /**
   * @param DefineRulesEvent $event
   * @return void
   */
  #[On(User::class, Model::EVENT_DEFINE_RULES, [self::class, 'requiresHandler'])]
  static public function onDefineRules(DefineRulesEvent $event): void {
    $settings = Plugin::getInstance()->getSettings()->passwordPolicy;

    if ($settings->usePwnedValidator) {
      array_unshift($event->rules, [['password', 'newPassword'], PwnedValidator::class]);
    }

    array_unshift($event->rules, [
      ['password', 'newPassword'],
      'match',
      'pattern' => helpers\Rule::getPattern(),
      'message' => \Craft::t(
        'lenz-craft-essentials',
        'Your password must contain at least one of each of the following: {rules}.',
        ['rules' => helpers\Rule::getMessage()]
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

  /**
   * @return bool
   */
  static public function requiresHandler(): bool {
    $settings = Plugin::getInstance()->getSettings();
    return $settings->passwordPolicy->enabled;
  }
}
