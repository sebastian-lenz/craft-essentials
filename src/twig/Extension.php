<?php

namespace lenz\craft\essentials\twig;

use Craft;
use craft\base\ElementInterface;
use Exception;
use lenz\craft\essentials\Plugin;
use lenz\craft\essentials\services\MailEncoder;
use lenz\craft\essentials\utils\Translations;
use lenz\craft\utils\elementCache\ElementCache;
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
   * @var array
   */
  private $_translations;

  /**
   * The cache key of the commit hash.
   */
  const CACHE_COMMIT = self::class . '::getCommitHash';


  /**
   * @inheritdoc
   */
  public function getFilters() {
    return [
      new TwigFilter('encodeMail', [$this, 'getEncodedMail']),
      new TwigFilter('translations', [$this, 'getTranslations']),
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

  /**
   * @return string
   */
  public function getCommitHash() {
    if (!isset($this->_commitHash)) {
      $this->_commitHash = ElementCache::with(self::CACHE_COMMIT, function() {
        try {
          return substr(shell_exec('git rev-parse HEAD'), 0, 7);
        } catch (Exception $e) {
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

  /**
   * @param array $options
   * @return array
   */
  public function getTranslations(ElementInterface $element = null, array $options = []) {
    if (is_null($element)) {
      $element = Craft::$app->getUrlManager()->getMatchedElement();
    }

    $id = $element->getId();
    if (!isset($this->_translations[$id])) {
      $this->_translations[$id] = Plugin::getInstance()
        ->translations
        ->getTranslations($element, $options);
    }

    return $this->_translations[$id];
  }
}
