<?php

namespace lenz\craft\essentials\services\i18n\sources;

use Craft;
use craft\web\View;
use Gettext\Extractors\PhpCode;
use lenz\contentfield\twig\YamlAwareTemplateLoader;
use lenz\craft\essentials\services\i18n\Translations;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use Twig\TwigFilter;
use yii\base\Exception;

/**
 * Class TemplatesSource
 */
class TemplatesSource extends AbstractSource
{
  /**
   * @var Environment|null
   */
  private $_twig = null;


  /**
   * @param Translations $translations
   * @throws Exception
   */
  public function extract(Translations $translations) {
    self::withSiteView(function(View $view) use ($translations) {
      $this->_twig = $this->resolveTwig($view);
      $this->extractTemplates($translations, $view->getTemplatesPath());
      $this->_twig = null;
    });
  }


  // Private methods
  // ---------------

  /**
   * @param Translations $translations
   * @param string $path
   * @param string $name
   * @throws \Exception
   */
  private function extractTemplate(Translations $translations, string $path, string $name) {
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
   * @throws \Exception
   */
  private function extractTemplates(Translations $translations, string $basePath) {
    $dirIterator = new RecursiveDirectoryIterator($basePath);
    $iterator    = new RecursiveIteratorIterator($dirIterator);

    foreach ($iterator as $path) {
      if (!preg_match('/\.twig$/', $path)) {
        continue;
      }

      $name = trim(substr($path, strlen($basePath)), DIRECTORY_SEPARATOR);
      $this->extractTemplate($translations, $path, $name);
    }
  }

  /**
   * @param View $view
   * @return \craft\web\twig\Environment
   * @throws Exception
   */
  private function resolveTwig(View $view) {
    $twig = class_exists(YamlAwareTemplateLoader::class)
      ? YamlAwareTemplateLoader::getSiteTwig($view)
      : $view->getTwig();

    $twig->addFilter(new TwigFilter('t', 'gettext'));
    return $twig;
  }

  /**
   * @param string $name
   * @param \Exception $error
   */
  private function reportError(string $name, \Exception $error) {
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
