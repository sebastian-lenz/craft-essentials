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
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Extension\EscaperExtension;
use Twig\Markup;
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
  private string $_commitHash;

  /**
   * The cache key of the commit hash.
   */
  const CACHE_COMMIT = self::class . '::getCommitHash';


  /**
   * Constructor
   */
  public function __construct() {
    \Craft::$app
      ->getView()
      ->getTwig()
      ->getExtension(EscaperExtension::class)
      ->setEscaper('html_entities', self::escapeHtmlEntities(...));
  }

  /**
   * @inheritDoc
   */
  public function getTokenParsers(): array {
    return [
      new tokenParsers\BufferedTokenParser(),
    ];
  }

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
   * @phpstan-return string
   */
  public function getCommitHash(): string {
    if (!isset($this->_commitHash)) {
      $this->_commitHash = ElementCache::with(self::CACHE_COMMIT, function() {
        try {
          if (!function_exists('shell_exec')) {
            throw new Exception('Function shell_exec missing');
          }

          return substr(@shell_exec('git rev-parse HEAD'), 0, 7);
        } catch (Exception) {
          return '0000000';
        }
      });
    }

    return $this->_commitHash;
  }

  /**
   * @phpstan-return string
   */
  public function getCurrentYear(): string {
    return date('Y');
  }

  /**
   * @phpstan-return void
   */
  public function interceptCache(): void {
    Plugin::getInstance()->frontendCache->intercept();
  }

  /**
   * @phpstan-param array|Attributes $value
   * @phpstan-return Attributes
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
   * @phpstan-param mixed $url
   * @phpstan-return Url
   */
  public function toUrl(mixed $url = ''): Url {
    return new Url($url);
  }

  /**
   * @phpstan-param Environment $env
   * @phpstan-param string $value
   * @phpstan-param string $charset
   * @phpstan-return string
   */
  static public function escapeHtmlEntities(Environment $env, string $value, string $charset = null): Markup {
    $result = htmlspecialchars($value, \ENT_QUOTES | \ENT_SUBSTITUTE, $charset);

    return new Markup(
      preg_replace('/&amp;(\w{3,6});/', '&$1;', $result),
      $charset
    );
  }
}
