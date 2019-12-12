<?php

namespace lenz\craft\essentials\structs\structure;

use ArrayIterator;
use craft\base\ElementInterface;
use craft\elements\db\ElementQuery;
use IteratorAggregate;

/**
 * Class AbstractStructure
 */
abstract class AbstractStructure implements IteratorAggregate
{
  /**
   * @var AbstractStructureItem[]
   */
  protected $_items = [];

  /**
   * @var string
   */
  const ITEM_CLASS = AbstractStructureItem::class;


  /**
   * AbstractMenu constructor.
   * @param ElementQuery $query
   */
  public function __construct(ElementQuery $query) {
    $elements  = $query->all();
    $itemClass = static::ITEM_CLASS;
    $items     = [];

    foreach ($elements as $element) {
      $item = new $itemClass($this, $element);
      $items[intval($item->id)] = $item;
    }

    $this->_items = $items;
  }

  /**
   * @return AbstractStructureItem[]
   */
  public function getAll() {
    return $this->_items;
  }

  /**
   * @param ElementInterface|AbstractStructureItem|int $elementOrId
   * @return AbstractStructureItem[]
   */
  public function getAncestors($elementOrId) {
    $child = $this->getById($elementOrId);
    if (is_null($child)) {
      return [];
    }

    return $this->filter(function(AbstractStructureItem $item) use ($child) {
      return (
        $item->nestedLft < $child->nestedLft &&
        $item->nestedRgt > $child->nestedRgt
      );
    });
  }

  /**
   * @param ElementInterface|AbstractStructureItem|int $elementOrId
   * @return AbstractStructureItem|mixed|null
   */
  public function getById($elementOrId) {
    if ($elementOrId instanceof AbstractStructureItem) {
      return $elementOrId;
    }

    $id = self::normalizeId($elementOrId);
    return array_key_exists($id, $this->_items)
      ? $this->_items[$id]
      : null;
  }

  /**
   * @param ElementInterface|AbstractStructureItem|int $elementOrId
   * @return AbstractStructureItem[]
   */
  public function getChildren($elementOrId) {
    $parent = $this->getById($elementOrId);
    if (is_null($parent)) {
      return [];
    }

    return $this->filter(function(AbstractStructureItem $item) use ($parent) {
      return (
        $item->nestedLft > $parent->nestedLft &&
        $item->nestedRgt < $parent->nestedRgt &&
        $item->nestedLevel === $parent->nestedLevel + 1
      );
    });
  }

  /**
   * @param ElementInterface|AbstractStructureItem|int $elementOrId
   * @return AbstractStructureItem[]
   */
  public function getDescendants($elementOrId) {
    $parent = $this->getById($elementOrId);
    if (is_null($parent)) {
      return [];
    }

    return $this->filter(function(AbstractStructureItem $item) use ($parent) {
      return (
        $item->nestedLft > $parent->nestedLft &&
        $item->nestedRgt < $parent->nestedRgt
      );
    });
  }

  /**
   * @inheritDoc
   */
  public function getIterator() {
    return new ArrayIterator($this->getRootItems());
  }

  /**
   * @param ElementInterface|AbstractStructureItem|int $elementOrId
   * @return AbstractStructureItem|null
   */
  public function getParent($elementOrId) {
    $child = $this->getById($elementOrId);
    if (is_null($child)) {
      return null;
    }

    foreach ($this->_items as $item) {
      if (
        $item->nestedLft < $child->nestedLft &&
        $item->nestedRgt > $child->nestedRgt &&
        $item->nestedLevel === $child->nestedLevel - 1
      ) {
        return $item;
      }
    }

    return null;
  }

  /**
   * @return AbstractStructureItem[]
   */
  public function getRootItems() {
    return $this->filter(function(AbstractStructureItem $item) {
      return $item->nestedLevel === 1;
    });
  }


  // Protected methods
  // -----------------

  /**
   * @param callable $callback
   * @return AbstractStructureItem[]
   */
  protected function filter(callable $callback) {
    return array_filter($this->_items, $callback);
  }


  // Static methods
  // --------------

  /**
   * @param ElementInterface|AbstractStructureItem|int $elementOrId
   * @return int
   */
  static function normalizeId($elementOrId) {
    if ($elementOrId instanceof ElementInterface) {
      return intval($elementOrId->getId());
    } elseif ($elementOrId instanceof AbstractStructureItem) {
      return $elementOrId->id;
    } else {
      return intval($elementOrId);
    }
  }
}
