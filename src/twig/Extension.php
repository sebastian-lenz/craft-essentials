<?php

namespace sebastianlenz\common\twig;

use sebastianlenz\common\MailEncoder;
use sebastianlenz\common\Plugin;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class Extension
 */
class Extension extends AbstractExtension
{
  /**
   * @var string
   */
  private $_commitHash;

  /**
   * The cache key of the commit hash.
   */
  const COMMIT_HASH_CACHE_KEY = 'sle.essentials.CommitHash';


  //<editor-fold desc="Twig extension implementation">

  /**
   * @inheritdoc
   */
  public function getFilters() {
    return [
      new TwigFilter('encodeMail', [self::class, 'encodeMail']),
    ];
  }

  /**
   * @inheritdoc
   */
  public function getFunctions() {
    return [
      new TwigFunction('commitHash', [$this, 'getCommitHash']),
      new TwigFunction('currentYear', [$this, 'getCurrentYear']),
      new TwigFunction('encodeMail', [$this, 'getEncodedMail']),
    ];
  }

  //</editor-fold>
  //<editor-fold desc="Helpers">

  /**
   * @return string
   */
  public function getCommitHash() {
    if (!isset($this->_commitHash)) {
      $cache = Plugin::getCache();
      $hash = $cache->get(self::COMMIT_HASH_CACHE_KEY);

      if ($hash === false) {
        try {
          $hash = substr(shell_exec('git rev-parse HEAD'), 0, 7);
          $cache->set(self::COMMIT_HASH_CACHE_KEY, $hash);
        } catch (\Exception $e) {
          $hash = '0000000';
        }
      }

      $this->_commitHash = $hash;
    }

    return $this->_commitHash;
  }

  /**
   * @return string
   */
  public function getCurrentYear() {
    return date('Y');
  }

  /**
   * @param string $value
   * @return string
   */
  public function getEncodedMail($value) {
    if (!is_string($value)) {
      return $value;
    }

    return MailEncoder::encode($value);
  }

  //</editor-fold>
}
