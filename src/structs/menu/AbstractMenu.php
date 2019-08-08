<?php

namespace lenz\craft\essentials\structs\menu;

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
   * @param int|string $type
   * @return AbstractMenuItem[]
   */
  public function getAllByType($type) {
    $isTypeId = is_numeric($type);

    return array_filter($this->_items, function(AbstractMenuItem $item) use ($isTypeId, $type) {
      return $isTypeId
        ? $item->typeId == $type
        : $item->typeHandle == $type;
    });
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
   * @param int|string $type
   * @return AbstractMenuItem|null
   */
  public function getByType($type) {
    $isTypeId = is_numeric($type);

    foreach ($this->_items as $item) {
      if ($isTypeId) {
        if ($item->typeId == $type) {
          return $item;
        }
      } elseif ($item->typeHandle == $type) {
        return $item;
      }
    }

    return null;
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


  // Protected methods
  // -----------------

  /**
   * Initializes the menu after loading.
   */
  protected function init() {}


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

      static::$_instance->init();
    }

    return static::$_instance;
  }

  /**
   * @param ElementInterface|AbstractMenuItem|int $elementOrId
   * @return int
   */
  static function normalizeId($elementOrId) {
    if ($elementOrId instanceof ElementInterface) {
      return intval($elementOrId->getId());
    } elseif ($elementOrId instanceof AbstractMenuItem) {
      return $elementOrId->id;
    } else {
      return intval($elementOrId);
    }
  }
}
