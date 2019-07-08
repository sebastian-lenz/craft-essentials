<?php

namespace lenz\craft\essentials\menu;

use craft\base\ElementInterface;
use craft\elements\db\ElementQuery;
use craft\elements\Entry;
use lenz\craft\utils\elementCache\ElementCache;

/**
 * Class Menu
 */
abstract class AbstractMenu
{
  /**
   * @var AbstractMenuItem[]
   */
  protected $_items = [];

  /**
   * @var AbstractMenu
   */
  static protected $_instance;

  /**
   * @var string
   */
  const ITEM_CLASS = AbstractMenuItem::class;


  /**
   * AbstractMenu constructor.
   * @param ElementQuery $query
   */
  public function __construct(ElementQuery $query) {
    $elements  = $query->all();
    $itemClass = static::ITEM_CLASS;
    $items     = [];
    $stack     = [];

    foreach ($elements as $element) {
      $item = new $itemClass($element);

      if ($element instanceof Entry) {
        $level = max(1, intval($element->level));
        while (count($stack) >= $level) {
          array_pop($stack);
        }

        $item->parentId = end($stack);
        $stack[] = $item->id;
      }

      $items[$item->id] = $item;
    }

    $this->_items = $items;
  }

  /**
   * @param ElementInterface|AbstractMenuItem|int $elementOrId
   * @param bool $includeSelf
   * @return AbstractMenuItem[]
   */
  public function getAncestors($elementOrId, $includeSelf = false) {
    $item   = $this->getById($elementOrId);
    $result = [];

    if (is_null($item)) {
      return $result;
    }

    if (!$includeSelf) {
      $item = $this->getById($item->parentId);
    }

    while ($item) {
      array_unshift($result, $item);
      $item = $this->getById($item->parentId);
    }

    return $result;
  }

  /**
   * @return AbstractMenuItem[]
   */
  public function getAll() {
    return $this->_items;
  }

  /**
   * @param ElementInterface|AbstractMenuItem|int $elementOrId
   * @return AbstractMenuItem|null
   */
  public function getById($elementOrId) {
    $id = static::normalizeId($elementOrId);
    return array_key_exists($id, $this->_items)
      ? $this->_items[$id]
      : null;
  }

  /**
   * @param ElementInterface|AbstractMenuItem|int $elementOrId
   * @return AbstractMenuItem[]
   */
  public function getChildren($elementOrId) {
    $id = static::normalizeId($elementOrId);

    return array_filter($this->_items, function(AbstractMenuItem $item) use ($id) {
      return $item->parentId === $id;
    });
  }

  /**
   * @return AbstractMenuItem[]
   */
  public function getRootItems() {
    return array_filter($this->_items, function(AbstractMenuItem $item) {
      return $item->parentId === false;
    });
  }


  // Static methods
  // --------------

  /**
   * @return static
   */
  static function getInstance() {
    if (!isset(static::$_instance)) {
      static::$_instance = ElementCache::withLanguage(self::class, function() {
        return new static(null);
      });
    }

    return static::$_instance;
  }

  /**
   * @param ElementInterface|AbstractMenuItem|int $elementOrId
   * @return int
   */
  static function normalizeId($elementOrId) {
    if ($elementOrId instanceof ElementInterface) {
      return $elementOrId->getId();
    } elseif ($elementOrId instanceof AbstractMenuItem) {
      return $elementOrId->id;
    } else {
      return intval($elementOrId);
    }
  }
}
