<?php

namespace lenz\craft\essentials\events;

use craft\elements\Entry;
use lenz\craft\essentials\FrontendCache;
use lenz\craft\essentials\Plugin;
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
   * @return \DateTime|null
   * @throws \Exception
   */
  private function getNextEntryChangeDate() {
    $now = new \DateTime();
    $nowAtom = $now->format(\DateTime::ATOM);

    $nextPost = Entry::find()
      ->status(null)
      ->postDate("> {$nowAtom}")
      ->orderBy('postDate')
      ->one();

    $nextExpiry = Entry::find()
      ->status(null)
      ->expiryDate("> {$nowAtom}")
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
   * @throws \Exception
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
    $cache = Plugin::getCache();
    $duration = $cache->get(self::CACHE_KEY);

    if ($duration === false) {
      try {
        $event = new CacheDurationEvent(
          $this->getDurationTillNextEntryChange()
        );

        Plugin::getInstance()->frontendCache->trigger(
          FrontendCache::EVENT_DEFAULT_CACHE_DURATION, $event
        );

        $duration = $event->duration;
      } catch (\Throwable $error) {
        $duration = 0;
      }

      $cache->set(self::CACHE_KEY, $duration, $duration);
    }

    return $duration;
  }

  /**
   * @param \DateTime $value
   */
  public function setMinDate(\DateTime $value) {
    $this->setMinDuration($value - time());
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
