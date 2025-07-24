<?php

namespace lenz\craft\essentials\services\loginSecurity;

use craft\controllers\UsersController;
use yii\base\ActionEvent;
use yii\base\Event;
use yii\base\InlineAction;
use yii\base\Module;
use yii\web\Application;
use yii\web\Response;

/**
 * Class Provider
 */
class Provider
{
  /**
   * @var int
   */
  const PASSWORD_RESET_COOLDOWN = 60 * 5;


  /**
   * @return void
   */
  static public function register(): void {
    Event::on(Application::class, Module::EVENT_AFTER_ACTION, [self::class, 'onAfterAction']);
    Event::on(Application::class, Module::EVENT_BEFORE_ACTION, [self::class, 'onBeforeAction']);
  }

  /**
   * @param ActionEvent $event
   * @return void
   */
  static public function onAfterAction(ActionEvent $event): void {
    if (
      $event->action instanceof InlineAction &&
      $event->action->controller instanceof UsersController &&
      $event->action->actionMethod == 'actionLogin'
    ) {
      self::hardenLoginResponse($event);
    }
  }

  /**
   * @param ActionEvent $event
   * @return void
   */
  static public function onBeforeAction(ActionEvent $event): void {
    if ($event->action instanceof InlineAction &&
      $event->action->controller instanceof UsersController &&
      $event->action->actionMethod == 'actionSendPasswordResetEmail'
    ) {
      self::applyRequestCooldown();
    }
  }


  // Private methods
  // ---------------

  /**
   * @return void
   */
  static public function applyRequestCooldown(): void {
    $remoteIps = \Craft::$app->cache->get(__METHOD__);
    $now = time();
    if (!is_array($remoteIps)) {
      $remoteIps = [];
    } else {
      $remoteIps = array_filter($remoteIps, fn($timestamp) => $now - $timestamp < self::PASSWORD_RESET_COOLDOWN);
    }

    $remoteIp = \Craft::$app->request->getRemoteIP();
    $isBlocked = array_key_exists($remoteIp, $remoteIps);
    $remoteIps[$remoteIp] = $now;
    \Craft::$app->cache->set(__METHOD__, $remoteIps);

    if ($isBlocked) {
      throw new \Exception(\Craft::t('app', 'Cooldown Time Remaining'));
    }
  }

  /**
   * @param ActionEvent $event
   * @return void
   */
  static private function hardenLoginResponse(ActionEvent $event): void {
    if (
      $event->result instanceof Response &&
      $event->result->format == Response::FORMAT_JSON &&
      is_array($event->result->data) &&
      array_key_exists('errorCode', $event->result->data)
    ) {
      $event->result->data = [
        'errorCode' => 'invalid_credentials',
        'message' => \Craft::t('app', 'Invalid username or password.'),
      ];
    }
  }
}
