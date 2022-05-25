<?php

namespace lenz\craft\essentials\services\gettext\utils;

use Exception;
use Gettext\Translations;
use Gettext\Utils\PhpFunctionsScanner as PhpFunctionsScannerBase;
use lenz\craft\essentials\services\gettext\Gettext;
use ReflectionClass;
use Throwable;
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

        if (is_array($token) && in_array($token[0], [T_STRING, T_NAME_QUALIFIED])) {
          $parts[] = $token[1];
        } elseif (is_array($token) && $token[0] == T_NS_SEPARATOR) {
          continue;
        } else {
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
   * @throws Throwable
   */
  protected function saveClass(array $translations, string $className) {
    if (
      !class_exists($className) ||
      !is_subclass_of($className, Model::class) ||
      is_subclass_of($className, UntranslatedModel::class)
    ) {
      return;
    }

    Gettext::printSource('model', $className);
    $reflector = new ReflectionClass($className);
    if ($reflector->isAbstract() || $reflector->getConstructor()->getNumberOfRequiredParameters() > 0) {
      return;
    }

    /** @var Model $model */
    $model = new $className();
    foreach ($model->attributes() as $attribute) {
      $label = $model->getAttributeLabel($attribute);
      foreach ($translations as $translation) {
        $row = $translation->insert('', $label);
        $row->addReference($reflector->getFileName());
      }
    }
  }

  /**
   * @param Translations[] $translations
   */
  protected function saveClasses(array $translations) {
    $this->eachClassName(function($className) use ($translations) {
      try {
        $this->saveClass($translations, $className);
      } catch (Throwable $error) {
        echo "Error: $error\n";
      }
    });
  }
}
