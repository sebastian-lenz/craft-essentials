<?php

namespace sebastianlenz\common;

use craft\events\RegisterCacheOptionsEvent;
use craft\services\Elements;
use craft\utilities\ClearCaches;
use yii\base\Event;
use yii\caching\FileCache;

/**
 * Class Plugin
 * @property FileCache $cache
 * @property FrontendCache $frontendCache
 * @property MailEncoder $mailEncoder
 * @method Settings getSettings()
 */
class Plugin extends \craft\base\Plugin
{
  /**
   * @return void
   */
  public function init() {
    parent::init();

    $this->setComponents([
      'cache' => [
        'class'     => FileCache::class,
        'cachePath' => '@runtime/elements'
      ],
      'frontendCache' => new FrontendCache(),
      'mailEncoder' => new MailEncoder(),
    ]);

    \Craft::$app->view->registerTwigExtension(new twig\Extension());

    Event::on(ClearCaches::class, ClearCaches::EVENT_REGISTER_CACHE_OPTIONS, [$this, 'onRegisterCacheOptions']);
    Event::on(Elements::class, Elements::EVENT_AFTER_SAVE_ELEMENT, [$this, 'onElementChanged']);
    Event::on(Elements::class, Elements::EVENT_AFTER_DELETE_ELEMENT, [$this, 'onElementChanged']);
    Event::on(Elements::class, Elements::EVENT_AFTER_MERGE_ELEMENTS, [$this, 'onElementChanged']);
    Event::on(Elements::class, Elements::EVENT_AFTER_SAVE_ELEMENT, [$this, 'onElementChanged']);
  }

  /**
   * @inheritdoc
   */
  public function createSettingsModel() {
    return new Settings();
  }

  /**
   * @param Event $event
   */
  public function onElementChanged(Event $event) {
    $this->cache->flush();
  }

  /**
   * @param RegisterCacheOptionsEvent $event
   */
  public function onRegisterCacheOptions(RegisterCacheOptionsEvent $event) {
    $event->options[] = [
      'key'    => 'elements',
      'label'  => 'Elements',
      'action' => [$this->cache, 'flush']
    ];
  }

  /**
   * @return FileCache
   */
  static function getCache() {
    return self::getInstance()->cache;
  }
}
