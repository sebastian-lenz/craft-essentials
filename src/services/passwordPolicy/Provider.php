<?php

namespace lenz\craft\essentials\services\passwordPolicy;

use craft\base\Model;
use craft\elements\User;
use lenz\craft\essentials\Plugin;
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
    $settings = Plugin::getInstance()->getSettings();
    if (!$settings->passwordPolicy->enabled) {
      return;
    }

    Event::on(
      User::class,
      Model::EVENT_DEFINE_RULES,
      PasswordRules::onDefineRules(...)
    );
  }
}
