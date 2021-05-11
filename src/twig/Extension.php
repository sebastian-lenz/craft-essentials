<?php

namespace lenz\craft\essentials\twig;

use Craft;
use craft\base\ElementInterface;
use Exception;
use lenz\craft\essentials\Plugin;
use lenz\craft\essentials\services\MailEncoder;
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
  public function getFilters(): array {
    return [
      new TwigFilter('encodeMail', [$this, 'getEncodedMail']),
      new TwigFilter('translations', [$this, 'getTranslations']),
    ];
  }

  /**
   * @inheritdoc
   */
  public function getFunctions(): array {
    return [
      new TwigFunction('commitHash', [$this, 'getCommitHash']),
      new TwigFunction('currentYear', [$this, 'getCurrentYear']),
      new TwigFunction('encodeMail', [$this, 'getEncodedMail']),
      new TwigFunction('interceptCache', [$this, 'interceptCache']),
      new TwigFunction('translations', [$this, 'getTranslations']),
    ];
  }

  /**
   * @return string
   */
  public function getCommitHash(): string {
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
  public function getCurrentYear(): string {
    return date('Y');
  }

  /**
   * @param string $value
   * @return string
   */
  public function getEncodedMail(string $value): string {
    if (!is_string($value)) {
      return $value;
    }

    return MailEncoder::encode($value);
  }

  /**
   * @param ElementInterface|null $element
   * @param array $options
   * @return array
   */
  public function getTranslations(ElementInterface $element = null, array $options = []): array {
    if (!($element instanceof ElementInterface)) {
      $element = Craft::$app->getUrlManager()->getMatchedElement();
    }

    $id = $element instanceof ElementInterface
      ? $element->getId()
      : '*';

    if (!isset($this->_translations[$id])) {
      $this->_translations[$id] = Plugin::getInstance()
        ->translations
        ->getTranslations($element, $options);
    }

    return $this->_translations[$id];
  }

  /**
   * @return void
   */
  public function interceptCache() {
    Plugin::getInstance()->frontendCache->intercept();
  }
}
