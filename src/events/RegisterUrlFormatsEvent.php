<?php

namespace lenz\craft\essentials\events;

use lenz\craft\essentials\services\redirectNotFound\formats\AssetUrlFormat;
use lenz\craft\essentials\services\redirectNotFound\formats\EntryUrlFormat;
use lenz\craft\essentials\services\redirectNotFound\formats\UrlFormat;
use yii\base\Event;

/**
 * Class RegisterUrlFormatsEvent
 */
class RegisterUrlFormatsEvent extends Event
{
  /**
   * @var UrlFormat[]
   */
  public array $formats;


  /**
   * @inheritDoc
   */
  public function __construct(array $config = []) {
    parent::__construct($config);

    if (!isset($this->formats)) {
      $this->formats = [
        new AssetUrlFormat(),
        new EntryUrlFormat(),
      ];
    }
  }
}
