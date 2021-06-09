<?php

namespace lenz\craft\essentials\events;

use craft\elements\Entry;
use DateTime;
use DateTimeZone;
use Exception;
use lenz\craft\essentials\Plugin;
use lenz\craft\essentials\services\frontendCache\FrontendCacheService;
use Throwable;
use yii\base\Event;

/**
 * Class CacheDurationEvent
 */
class CacheDurationEvent extends Event
{
  /**
   * @var int
   */
  public $duration = 0;

  /**
   * Key used to store the default cache duration.
   */
  const CACHE_KEY = 'common.cacheDuration';


  /**
   * FrontendCacheRequestEvent constructor.
   * @param int|false $duration
   */
  public function __construct($duration = false) {
    parent::__construct();

    $this->duration = $duration === false
      ? $this->getDefaultDuration()
      : $duration;
  }

  /**
   * @return DateTime|null
   * @throws Exception
   */
  private function getNextEntryChangeDate() {
    $now = (new DateTime('now', new DateTimeZone('UTC')))
      ->format('Y-m-d H:i:s');

    $nextPost = Entry::find()
      ->status(null)
      ->postDate("> $now")
      ->orderBy('postDate')
      ->one();

    $nextExpiry = Entry::find()
      ->status(null)
      ->expiryDate("> $now")
      ->orderBy('expiryDate')
      ->one();

    $result = !is_null($nextPost) && !is_null($nextPost->postDate)
      ? $nextPost->postDate
      : null;

    if (!is_null($nextExpiry) && !is_null($nextExpiry->expiryDate)) {
      if (is_null($result)) {
        $result = $nextExpiry->expiryDate;
      } else {
        $result = $result->getTimestamp() > $nextExpiry->expiryDate->getTimestamp()
          ? $nextExpiry->expiryDate
          : $result;
      }
    }

    return $result;
  }

  /**
   * @return int
   * @throws Exception
   */
  private function getDurationTillNextEntryChange() {
    $entry = $this->getNextEntryChangeDate();

    if (is_null($entry)) {
      return 0;
    } else {
      return $entry->getTimestamp() - time();
    }
  }

  /**
   * @return int
   */
  private function getDefaultDuration() {
    $cache = Plugin::getInstance()->elementCache->cache;
    $duration = $cache->get(self::CACHE_KEY);

    if ($duration === false) {
      try {
        $event = new CacheDurationEvent(
          $this->getDurationTillNextEntryChange()
        );

        Plugin::getInstance()->frontendCache->trigger(
          FrontendCacheService::EVENT_DEFAULT_CACHE_DURATION, $event
        );

        $duration = $event->duration;
      } catch (Throwable $error) {
        $duration = 0;
      }

      $cache->set(self::CACHE_KEY, $duration, $duration);
    }

    return $duration;
  }

  /**
   * @param DateTime $value
   * @noinspection PhpUnused
   */
  public function setMinDate(DateTime $value) {
    $this->setMinDuration($value->getTimestamp() - time());
  }

  /**
   * @param int $value
   */
  public function setMinDuration($value) {
    if ($value <= 0) return;
    if ($this->duration == 0) {
      $this->duration = $value;
    } else {
      $this->duration = min($this->duration, $value);
    }
  }
}
