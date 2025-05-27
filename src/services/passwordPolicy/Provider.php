<?php

namespace lenz\craft\essentials\services\passwordPolicy;

use craft\base\Model;
use craft\elements\User;
use lenz\craft\essentials\services\AbstractProvider;
use lenz\craft\essentials\services\passwordPolicy\listeners\PasswordRules;
use yii\base\Event;

/**
 * Class Provider
 */
class Provider extends AbstractProvider
{
  /**
   * @inheritDoc
   */
  public static function register(): void {
    Event::on(
      User::class,
      Model::EVENT_DEFINE_RULES,
      PasswordRules::onDefineRules(...)
    );
  }
}
