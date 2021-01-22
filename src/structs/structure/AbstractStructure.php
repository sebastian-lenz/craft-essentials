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
   *
   * @param ElementQuery|null $query
   */
  public function __construct(ElementQuery $query = null) {
    if (!is_null($query)) {
      $this->_items = $this->createItemsFromQuery($query);
    }
  }

  /**
   * @param AbstractStructureItem $parent
   * @param AbstractStructureItem $item
   * @return AbstractStructureItem
   * @noinspection PhpUnused (Public API)
   */
  public function attachTo(AbstractStructureItem $parent, AbstractStructureItem $item): AbstractStructureItem {
    $insertAt = $parent->nestedLft;
    $insertIndex = -1;

    foreach ($this->_items as $index => $existing) {
      if ($existing->nestedLft == $insertAt) $insertIndex = $index;
      if ($existing->nestedLft > $insertAt) $existing->nestedLft += 2;
      if ($existing->nestedRgt > $insertAt) $existing->nestedRgt += 2;
    }

    $item->nestedLevel = $parent->nestedLevel + 1;
    $item->nestedLft = $insertAt + 1;
    $item->nestedRgt = $insertAt + 2;

    if ($insertIndex == -1) {
      $this->_items[] = $item;
    } else {
      array_splice($this->_items, $insertIndex, 0, [$item]);
    }

    return $item;
  }

  /**
   * @return AbstractStructureItem[]
   */
  public function getAll(): array {
    return $this->_items;
  }

  /**
   * @param ElementInterface|AbstractStructureItem|int $elementOrId
   * @return AbstractStructureItem[]
   */
  public function getAncestors($elementOrId): array {
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
   * @return AbstractStructureItem|null
   */
  public function getById($elementOrId): ?AbstractStructureItem {
    if ($elementOrId instanceof AbstractStructureItem) {
      return $elementOrId;
    }

    $id = self::normalizeId($elementOrId);
    foreach ($this->_items as $item) {
      if ($item->id == $id) return $item;
    }

    return null;
  }

  /**
   * @param ElementInterface|AbstractStructureItem|int $elementOrId
   * @return AbstractStructureItem[]
   */
  public function getChildren($elementOrId): array {
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
  public function getDescendants($elementOrId): array {
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
  public function getParent($elementOrId): ?AbstractStructureItem {
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
  public function getRootItems(): array {
    return $this->filter(function(AbstractStructureItem $item) {
      return $item->nestedLevel === 1;
    });
  }


  // Protected methods
  // -----------------

  /**
   * @param ElementQuery $query
   * @return AbstractStructureItem[]
   */
  protected function createItemsFromQuery(ElementQuery $query): array {
    $elements = $query->all();
    $itemClass = static::ITEM_CLASS;
    $items = [];

    foreach ($elements as $element) {
      $item = new $itemClass($this, $element);
      $items[intval($item->id)] = $item;
    }

    return $items;
  }

  /**
   * @param callable $callback
   * @return AbstractStructureItem[]
   */
  protected function filter(callable $callback): array {
    return array_values(array_filter($this->_items, $callback));
  }


  // Static methods
  // --------------

  /**
   * @param ElementInterface|AbstractStructureItem|int $elementOrId
   * @return int
   */
  static function normalizeId($elementOrId): int {
    if ($elementOrId instanceof ElementInterface) {
      return intval($elementOrId->getId());
    } elseif ($elementOrId instanceof AbstractStructureItem) {
      return $elementOrId->id;
    } else {
      return intval($elementOrId);
    }
  }
}
