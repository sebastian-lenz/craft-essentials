<?php

namespace lenz\craft\essentials\services\gettext\sources;

use Craft;
use craft\web\twig\Environment;
use craft\web\View;
use Exception;
use Gettext\Extractors\PhpCode;
use lenz\contentfield\twig\YamlAwareTemplateLoader;
use lenz\craft\essentials\services\gettext\Gettext;
use lenz\craft\essentials\services\gettext\utils\Translations;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use Twig\TwigFilter;

/**
 * Class TemplatesSource
 */
class TemplatesSource extends AbstractSource
{
  /**
   * @var array{name: string, path: string}
   */
  public $roots;

  /**
   * @var Environment|null
   */
  private $_twig = null;


  /**
   * @param Gettext $gettext
   */
  public function __construct(Gettext $gettext) {
    parent::__construct($gettext);
    $this->roots = $this->parseSiteRoots();
  }

  /**
   * @inheritDoc
   * @throws Exception
   */
  public function extract(Translations $translations) {
    self::withSiteView(function(View $view) use ($translations) {
      $this->_twig = $this->resolveTwig($view);
      foreach ($this->roots as $root) {
        $this->extractTemplates($translations, $root['path']);
      }
      $this->_twig = null;
    });
  }


  // Private methods
  // ---------------

  /**
   * @param Translations $translations
   * @param string $path
   * @param string $name
   * @throws Exception
   */
  private function extractTemplate(Translations $translations, string $path, string $name) {
    Gettext::printSource('template', $path);

    try {
      $source = $this->_twig->getLoader()->getSourceContext($name);
      $code = $this->_twig->compileSource($source);

      PhpCode::fromString($code, $translations, [
        'file' => $path,
        'functions' => [
          'gettext' => 'gettext',
        ]
      ]);

    } catch (SyntaxError $error) {
      $this->reportError($name, $error);
    } catch (LoaderError $error) {
      $this->reportError($name, $error);
    }
  }

  /**
   * @param Translations $translations
   * @param string $basePath
   * @throws Exception
   */
  private function extractTemplates(Translations $translations, string $basePath) {
    $dirIterator = new RecursiveDirectoryIterator($basePath);
    $iterator    = new RecursiveIteratorIterator($dirIterator);

    foreach ($iterator as $path) {
      if (!preg_match('/\.twig$/', $path) || $this->_gettext->isFileExcluded($path)) {
        continue;
      }

      $name = trim(substr($path, strlen($basePath)), DIRECTORY_SEPARATOR);
      $this->extractTemplate($translations, $path, $name);
    }
  }

  /**
   * @return array
   */
  private function parseSiteRoots(): array {
    $roots = [];

    foreach (Craft::$app->view->getSiteTemplateRoots() as $name => $paths) {
      foreach ((is_array($paths) ? $paths : [$paths]) as $path) {
        $roots[] = [
          'name' => $name,
          'path' => $path,
        ];
      }
    }

    return $roots;
  }

  /**
   * @param View $view
   * @return Environment
   * @throws Exception
   */
  private function resolveTwig(View $view): Environment {
    $twig = class_exists(YamlAwareTemplateLoader::class)
      ? YamlAwareTemplateLoader::getSiteTwig($view)
      : $view->getTwig();

    $twig->addFilter(new TwigFilter('t', 'gettext'));
    return $twig;
  }

  /**
   * @param string $name
   * @param Exception $error
   */
  private function reportError(string $name, Exception $error) {
    echo implode("\n", [
      '',
      'Syntax error in `' . $name . '`:',
      '  ' . $error->getMessage(),
    ]);
  }


  // Static methods
  // --------------

  /**
   * @param callable $callback
   * @throws Exception
   */
  static public function withSiteView(callable $callback) {
    $view = Craft::$app->getView();
    $templateMode = $view->getTemplateMode();
    $view->setTemplateMode(View::TEMPLATE_MODE_SITE);

    $callback($view);

    $view->setTemplateMode($templateMode);
  }
}
