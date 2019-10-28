<?php

namespace lenz\craft\essentials\services\gettext\utils;

use Exception;
use Gettext\Translations;
use Gettext\Utils\PhpFunctionsScanner as PhpFunctionsScannerBase;
use lenz\craft\essentials\services\gettext\Gettext;
use yii\base\Model;

/**
 * Class PhpFunctionsScanner
 */
class PhpFunctionsScanner extends PhpFunctionsScannerBase
{
  /**
   * @param Translations|Translations[] $translations
   * @param array $options
   * @throws Exception
   */
  public function save($translations, array $options) {
    $translations = is_array($translations)
      ? $translations
      : [$translations];

    $this->saveGettextFunctions($translations, $options);
    $this->saveClasses($translations);
  }


  // Protected methods
  // -----------------

  /**
   * @param callable $callback
   */
  protected function eachClassName(callable $callback) {
    $namespace = '';
    $count = count($this->tokens);
    $index = 0;
    $depth = 0;

    $readString = function() use (&$index, $count) {
      while ($index < $count) {
        $token = $this->tokens[$index++];
        if ($token[0] == T_STRING) {
          return $token[1];
        }
      }

      return null;
    };

    $readNamespace = function() use (&$index, $count) {
      $parts = [];
      while ($index < $count) {
        $token = $this->tokens[$index++];
        if ($token[0] == T_STRING) {
          $parts[] = $token[1];
        } elseif ($token[0] == T_NS_SEPARATOR) {
          continue;
        } elseif (count($parts)) {
          break;
        }
      }

      return count($parts) ? implode('\\', $parts) : null;
    };

    while ($index < $count) {
      $token = $this->tokens[$index++];
      if ($token == '{') {
        $depth += 1;
      } elseif ($token == '}') {
        $depth -= 1;
      } else if ($depth == 0 && $token[0] == T_NAMESPACE) {
        $namespace = $readNamespace();
      } elseif ($depth == 0 && $token[0] == T_CLASS) {
        $className = $readString();
        if (!is_null($className)) {
          $callback($namespace . '\\' . $className);
        }
      }
    }
  }

  /**
   * @param Translations[] $translations
   * @param string $className
   */
  protected function saveClass(array $translations, string $className) {
    if (!class_exists($className) || !is_subclass_of($className, Model::class)) {
      return;
    }

    Gettext::printSource('model', $className);

    /** @var Model $model */
    $model = new $className();
    foreach ($model->attributes() as $attribute) {
      $label = $model->getAttributeLabel($attribute);
      foreach ($translations as $translation) {
        $translation->insert('', $label);
      }
    }
  }

  /**
   * @param Translations[] $translations
   */
  protected function saveClasses(array $translations) {
    $this->eachClassName(function($className) use ($translations) {
      $this->saveClass($translations, $className);
      try {

      } catch (\Throwable $error) {
        // Ignore errors here
      }
    });
  }
}
