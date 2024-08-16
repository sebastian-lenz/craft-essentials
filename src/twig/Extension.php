<?php

namespace lenz\craft\essentials\twig;

use Exception;
use lenz\craft\essentials\helpers\ElementHelper;
use lenz\craft\essentials\helpers\HtmlHelper;
use lenz\craft\essentials\Plugin;
use lenz\craft\essentials\services\MailEncoder;
use lenz\craft\essentials\services\translations\Translations;
use lenz\craft\utils\elementCache\ElementCache;
use lenz\craft\utils\models\Attributes;
use lenz\craft\utils\models\Url;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use yii\web\BadRequestHttpException;

/**
 * Class Extension
 */
class Extension extends AbstractExtension
{
  /**
   * @var string
   */
  private string $_commitHash;

  /**
   * The cache key of the commit hash.
   */
  const CACHE_COMMIT = self::class . '::getCommitHash';


  /**
   * @inheritdoc
   */
  public function getFilters(): array {
    return [
      new TwigFilter('compose', [Url::class, 'compose']),
      new TwigFilter('eagerLoad', [ElementHelper::class, 'eagerLoad']),
      new TwigFilter('encodeMail', [MailEncoder::class, 'encode']),
      new TwigFilter('translations', [Translations::class, 'forElement']),
    ];
  }

  /**
   * @inheritdoc
   */
  public function getFunctions(): array {
    return [
      new TwigFunction('cx', [HtmlHelper::class, 'joinClassNames']),
      new TwigFunction('commitHash', [$this, 'getCommitHash']),
      new TwigFunction('currentYear', [$this, 'getCurrentYear']),
      new TwigFunction('encodeMail', [MailEncoder::class, 'encode']),
      new TwigFunction('fixture', [Fixture::class, 'get']),
      new TwigFunction('interceptCache', [$this, 'interceptCache']),
      new TwigFunction('toAttributes', [$this, 'toAttributes']),
      new TwigFunction('toUrl', [$this, 'toUrl']),
      new TwigFunction('translations', [Translations::class, 'forElement']),
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
        } catch (Exception) {
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
   * @return void
   */
  public function interceptCache(): void {
    Plugin::getInstance()->frontendCache->intercept();
  }

  /**
   * @param array|Attributes $value
   * @return Attributes
   */
  public function toAttributes(mixed $value = []): Attributes {
    if ($value instanceof Attributes) {
      return $value;
    } elseif (is_array($value)) {
      return new Attributes($value);
    }

    return new Attributes();
  }

  /**
   * @param array|Attributes $value
   * @return Attributes
   */
  public function toUrl(mixed $url = ''): Url {
    return new Url($url);
  }
}
