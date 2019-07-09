<?php

namespace lenz\craft\essentials\utils;

use Craft;
use craft\fields\Matrix;
use lenz\craft\utils\elementCache\ElementCache;

/**
 * Trait IdentifiersTrait
 */
trait IdentifiersTrait
{
  /**
   * @var array
   */
  static private $_identifiers;


  /**
   * @param string $name
   * @return int|null
   */
  static public function __callStatic($name, $arguments) {
    self::loadIdentifiers();

    return array_key_exists($name, self::$_identifiers)
      ? self::$_identifiers[$name]
      : null;
  }

  /**
   * @return array
   */
  static public function getAllIdentifiers() {
    $handles = [];

    // Section and entry type ids
    $sections = Craft::$app->getSections()->getAllSections();
    foreach ($sections as $section) {
      $handles['section' . ucfirst($section->handle)] = intval($section->id);

      foreach ($section->getEntryTypes() as $entryType) {
        $handles['type' . ucfirst($entryType->handle)] = intval($entryType->id);
      }
    }

    // Category ids
    $categories = Craft::$app->getCategories()->getAllGroups();
    foreach ($categories as $category) {
      $handles['category' . ucfirst($category->handle)] = intval($category->id);
    }

    // Matrix block type ids
    $fields = Craft::$app->getFields()->getAllFields();
    $matrix = Craft::$app->getMatrix();
    foreach ($fields as $field) {
      if ($field instanceof Matrix) {
        $blocks = $matrix->getBlockTypesByFieldId($field->id);
        foreach ($blocks as $block) {
          $handles['matrix' . ucfirst($field->handle) . ucfirst($block->handle)] = intval($block->id);
        }
      }
    }

    return $handles;
  }

  /**
   * @return string
   */
  static public function getIdentifierDocComment() {
    self::loadIdentifiers();

    $lines = [];
    $identifiers = array_keys(self::$_identifiers);
    sort($identifiers);

    foreach ($identifiers as $identifier) {
      $lines[] = ' * @method static int ' . $identifier . '()';
    }

    return implode("\n", $lines);
  }

  /**
   * @return void
   */
  static public function loadIdentifiers() {
    if (!isset(self::$_identifiers)) {
      self::$_identifiers = ElementCache::with(self::class, function() {
        return self::getAllIdentifiers();
      });
    }
  }
}
