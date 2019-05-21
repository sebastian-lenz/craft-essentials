<?php

namespace lenz\craft\essentials\twig;

use lenz\craft\essentials\services\MailEncoder;
use lenz\craft\utils\elementCache\ElementCache;

/**
 * Class Extension
 */
class Extension extends \Twig_Extension
{
  /**
   * @var string
   */
  private $_commitHash;

  /**
   * The cache key of the commit hash.
   */
  const CACHE_COMMIT = self::class . '::getCommitHash';


  /**
   * @inheritdoc
   */
  public function getFilters() {
    return [
      new \Twig_SimpleFilter('encodeMail', [self::class, 'encodeMail']),
    ];
  }

  /**
   * @inheritdoc
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('commitHash', [$this, 'getCommitHash']),
      new \Twig_SimpleFunction('currentYear', [$this, 'getCurrentYear']),
      new \Twig_SimpleFunction('encodeMail', [$this, 'getEncodedMail']),
    ];
  }

  /**
   * @return string
   */
  public function getCommitHash() {
    if (!isset($this->_commitHash)) {
      $this->_commitHash = ElementCache::with(self::CACHE_COMMIT, function() {
        try {
          return substr(shell_exec('git rev-parse HEAD'), 0, 7);
        } catch (\Exception $e) {
          return '0000000';
        }
      });
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
}
